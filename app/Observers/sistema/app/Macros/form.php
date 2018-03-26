<?php

Form::macro('fastEdit', function($fieldName, $label){

    $htmlContent = Form::text($fieldName, null, ['placeholder'=>$label]);
    $htmlContent.= Form::button('<i class="fa fa-check"></i>', [
        'type'  =>'submit',
        'class' =>'btn btn-xs btn-success',
        'title' =>'Clique para salvar'
    ]);

    return $htmlContent;

});

Form::macro('selectUfs', function($fieldName, $selected=null,$attributes=[]){
    $attributes['name'] = $fieldName;
    $select = "<select" . Html::attributes($attributes).">";
    $select.= "<option value=''>Estado</option>";
    foreach(config('address.ufs') as $key=>$uf){
        $select.= "<option data-uf='" . $uf. "' value='".$key."' " . ($key==$selected?"selected":"") . ">"
                    .$uf
                 ."</option>";
    }
    return $select."</select>";
});

Form::macro('selectEstados', function($fieldName, $selected=null,$attributes=[]){

    $ufs = trans('checkout::address.ufs');

    if ($selected) {
        if (!is_numeric($selected)) {
            $selected = array_search($selected, $ufs);
        }
    }

    $attributes['name'] = $fieldName;
    $attributes['id'] = isset($attributes['id']) ? $attributes['id'] : $fieldName;
    $select = "<select" . Html::attributes($attributes).">";
    $select.= "<option value=''>Estado</option>";
    foreach($ufs as $key=>$uf){
        $select.= "<option data-uf='" . $uf. "' value='".$key."' " . ($key==$selected?"selected":"") . ">"
                    . $uf
                 ."</option>";
    }
    return $select."</select>";
});

Form::macro('group', function($fieldType, $fieldName="", $label="", $value=null, $fieldAttributes=null) {

    if (!$label) {
        $label = humanize( $fieldName );
    }

    if (isset($fieldAttributes['label'])) {
        $label = humanize( $fieldAttributes['label'] );
    }

    if ($fieldType == "label") {
        return Form::label( mb_strtoupper( $label ), null, ["style"=>"margin-top:10px; display:block; border-bottom:1px solid;"] );
    }
    if ($fieldType == "hidden") {
        return Form::hidden($fieldName, $value, $fieldAttributes);
    }

    if (!isset($fieldAttributes['class'])) {
        $fieldAttributes['class'] = "";
    }

    if(!in_array($fieldType, ["radio", "checkbox"])) {
        $fieldAttributes['class'] .= "form-control";
    }

    $value = $value ?: Form::getValueAttribute($fieldName);

    $fieldAttributes["data-value"] = Form::getValueAttribute($fieldName);

    $groupContent   = "<div class='form-group'>";
    $fieldHtml      = Form::label($fieldName, $label, ['class'=>'control-label']) . " ";
    $htmlContent    = "";

    if ($fieldType == "checkboxGroup") {
        $class    = isset($fieldAttributes['label']) ? str_slug($fieldAttributes['label']) : $fieldName;
        $htmlContent.= " [ <a href=\"javascript:$('.$class input').prop('checked', true)\" title='Marcar Todos'><i style='vertical-align: text-bottom;' class='fa fa-check-square-o'></i></a> ";
        $htmlContent.= " / <a href=\"javascript:$('.$class input').prop('checked', false)\" title='Desmarcar Todos'><i style='vertical-align: text-bottom;' class='fa fa-square-o'></i></a> ] ";
    }

    $htmlContent.= Form::errors($fieldName);
    if ($fieldType == 'select') {
        $defaultValue = null;

        if(isset($fieldAttributes['value'])){
            $defaultValue = $fieldAttributes['value'];
            unset($fieldAttributes['value']);
        }

        $htmlContent.= Form::select($fieldName, $value, $defaultValue, $fieldAttributes);
    } elseif(in_array($fieldType, ["radio", "checkbox"])) {
        $checked = null;
        if(isset($fieldAttributes['checked']) && is_bool(isset($fieldAttributes['checked']))){
            $checked = $fieldAttributes['checked'];
        }
        $htmlContent.= Form::$fieldType($fieldName, $value, $checked, $fieldAttributes);
    } else {
        $htmlContent.= Form::$fieldType($fieldName, $value, $fieldAttributes);
    }

    if(in_array($fieldType, ["radio", "checkbox"])){
        $groupContent .= $htmlContent . " " . $fieldHtml;
    } else {
        $groupContent .= $fieldHtml . " " . $htmlContent;
    }

    $groupContent.= "</div>";

    return $groupContent;
});

Form::macro('static', function($fieldName, $value, $attributes){
    if (isset($fieldAttributes['class'])) {
        $fieldAttributes['class'] .= " form-control-static";
    } else {
        $fieldAttributes['class'] = "form-control-static";
    }

    $htmlContent = "<div class='" . $fieldAttributes['class'] . "'>";
    $htmlContent.= $value;
    $htmlContent.= "</div>";

    return $htmlContent;
});

Form::macro('errors', function($fieldName){
    $errors = view()->getShared()['errors'];
    return $errors->first($fieldName, '<div class="label label-block label-danger">:message</div>');
});

Form::macro('imageUpload', function($fieldName, $value, $attributes) {
    $attributes = array_merge($attributes, [
        "id"=>$fieldName
    ]);

    $value = $value ?: "upload/default.jpg";

    return "<div class='input-group image-upload'>" .
                Form::text($fieldName, $value, $attributes) .
                "<span class='input-group-btn'>
                    <button class='btn btn-default popup_selector' type='button' data-inputid='$fieldName'>
                        <i class='fa fa-search'></i>
                    </button>
                </span>
            </div>
            <div class='image-preview'>
                <img src='" . asset( $value ) . "' class='img-responsive center-block'>
            </div>";
});

Form::macro('fileUpload', function($fieldName, $value, $attributes) {
    $attributes = array_merge($attributes, [
        "id"=>$fieldName
    ]);

    return "<div class='input-group'>" .
                Form::text($fieldName, $value, $attributes) .
                "<span class='input-group-btn'>
                    <button class='btn btn-default popup_selector' type='button' data-inputid='$fieldName'>
                        <i class='fa fa-search'></i>
                    </button>
                </span>
            </div>";
});

Form::macro("checkboxGroup", function($fieldName, $values=[], $attributes=[]){
    $class    = isset($attributes['label']) ? str_slug($attributes['label']) : $fieldName;
    $contents = "<div class='row $class'>";
    $columns  = isset($attributes["data-split"]) ? $attributes["data-split"] : 1;
    $columnSize = 12/$columns;

    foreach ($values as $key=>$value) {
        $contents.= "<div class='checkbox col-md-{$columnSize}'>
                        <label>".
                            Form::checkbox("{$fieldName}[]", $key) .
                            "{$value}
                        </label>
                      </div>";
    }

    return $contents . "</div>";

});

Form::macro("radioGroup", function($fieldName, $values=[], $attributes=[]){

    $selectedValue = isset($attributes["data-value"]) ? $attributes["data-value"] : array_first(array_keys($values));
    $contents = "<div class='row'><div class='col-xs-12'>";
    $class = isset($attributes["inline"]) ? "radio-inline" : null;

    foreach ($values as $key=>$value) {
        $radio = "<label class='$class'>" .
                    Form::radio("{$fieldName}", $key, $key == $selectedValue) .
                    "{$value}
                  </label>";
        if (is_null($class)) {
            $contents.= "<div class='radio'> $radio </div>";
        } else {
            $contents.= $radio;
        }
    }

    return $contents . "</div></div>";

});

Form::macro("datepicker", function($fieldName, $value=null, $attributes=[]){

    $attributes["date-format"] = "dd/mm/yyyy";
    $attributes["data-datepicker"] = true;

    return Form::input("text", $fieldName, $value, $attributes);
});

Form::macro("monetary", function($fieldName, $value=null, $attributes=[]){

    $attributes["data-inputmoney"] = true;
    return Form::input("text", $fieldName, $value, $attributes);

});