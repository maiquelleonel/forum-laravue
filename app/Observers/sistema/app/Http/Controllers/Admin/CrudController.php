<?php

namespace App\Http\Controllers\Admin;

use App\Domain\FormFields\TextField;
use App\Entities\User;
use Artesaos\Defender\Exceptions\ForbiddenException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Http\Requests\Admin\Request;

use App\Http\Requests;
use App\Support\SiteSettings;

abstract class CrudController extends Controller
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var string
     */
    protected $modelName;

    /**
     * @var array Columns
     */
    protected $columns = [];

    /**
     * @var bool
     */
    protected $deleteAction = false;

    /**
     * @var bool
     */
    protected $duplicateAction = false;

    /**
     * @var bool
     */
    protected $createAction = true;

    /**
     * @var Model
     */
    protected $lastInsertedModel;

    /**
     * @var Model
     */
    protected $lastUpdatedModel;

    /**
     * @var array
     */
    protected $dataShared = [];

    /**
     * @var integer
     */
    protected $register_per_page = 10;

    /**
     * @var array
     */
    protected $eagerLoading = [];

    /**
     * @var string
     */
    protected $backAction;

    /**
     * @var bool
     */
    protected $showId = true;

    /**
     * @var array Sort By
     */
    protected $sortBy = ["id"];

    /**
     * @var string Sort Order
     */
    protected $sortDirection = "asc";

    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
    protected $authField = "user_id";

    /**
     * CrudController constructor.
     * @param SiteSettings $siteSettings
     * @param Model $model
     */
    public function __construct(SiteSettings $siteSettings, Model $model)
    {
        parent::__construct($siteSettings);
        $this->model        = $model;
        $this->modelName    = $this->modelName ?: $model->getTable();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $columns = $this->getColumns() ?: $this->columns ?: $this->model->getFillable();
        if($this->authField && $this->user){
            $this->model = $this->model->where($this->authField, $this->user->id);
        }
        foreach ($this->sortBy as $column) {
            $this->model = $this->model->orderBy($column, $this->sortDirection);
        }
        $model = $this->model->with($this->eagerLoading)->paginate( $this->register_per_page );
        $header = $this->modelName;
        $editAction = get_class( $this ) . "@edit";
        $createAction = $this->createAction ? get_class( $this ) . "@create" : null;
        $duplicateAction = $this->duplicateAction ? get_class( $this ) . "@duplicate" : null;
        $deleteAction = $this->deleteAction ? get_class( $this ) . "@destroy" : null;
        $data   = (Object) $this->dataShared;
        $showId = $this->showId;

        return view($this->viewName("index"), compact(
            "model", "header", "columns", "editAction", "createAction", "deleteAction", "duplicateAction", "data", "showId"
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $header = $this->modelName;
        $formAction = get_class( $this ) . "@store";
        $backAction = $this->backAction ?: get_class( $this ) . "@index";
        $fields = $this->getFields();
        $data   = (Object) $this->dataShared;

        return view($this->viewName("create"), compact("header", "formAction", "backAction", "fields", "data"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->lastInsertedModel = $this->model->create( $request->all() );
        \Cache::flush();
        if ($this->backAction) {
            return redirect()->to($this->backAction);
        }
        return redirect()->action( get_class( $this ) . "@index" );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function duplicate($id)
    {
        $model = $this->model->find( $id );

        $this->checkAuthUser($model);

        return redirect()->action(get_class( $this ) . "@create")
                         ->withInput( $model->getAttributes() );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $model = $this->model->with($this->eagerLoading)->find( $id );
        $this->checkAuthUser($model);
        $header= $this->modelName;
        $formAction = get_class( $this ) . "@update";
        $backAction = $this->backAction ?: get_class( $this ) . "@index";
        $fields = $this->getFields();
        $data   = (Object) $this->dataShared;

        return view($this->viewName("edit"), compact("model", "header", "formAction", "backAction", "fields", "data"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @throws ForbiddenException
     */
    public function update(Request $request, $id)
    {
        $model = $this->model->find( $id );
        $this->checkAuthUser($model);
        $model->update( $request->all() );
        $this->lastUpdatedModel = $model;
        \Cache::flush();

        if ($this->backAction) {
            return redirect()->to($this->backAction);
        }

        return redirect()->action( get_class( $this ) . "@index" );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = $this->model->find($id);
        $this->checkAuthUser($model);
        $model->delete();
        return back();
    }

    protected function viewName($name)
    {
        if (view()->exists("admin.pages.".$this->modelName.".".$name) ) {
            return "admin.pages.".$this->modelName.".".$name;
        }
        return "admin.pages.crud.".$name;
    }

    public function getFields()
    {
        $fieldsNames = $this->model->getFillable();
        $fields = [];
        foreach ($fieldsNames as $fieldName) {
            $fields[] = new TextField( $fieldName );
        }
        return $fields;
    }

    public function getColumns()
    {
        return [];
    }

    public function checkAuthUser($model)
    {
        if ($this->authField && $this->user) {
            if($model->{$this->authField} == $this->user->id){
                return;
            }
            throw new ForbiddenException;
        }

        return;
    }

    protected function getDateInterval(Request $request)
    {
        if ($request->has('from')) {
            $from = Carbon::createFromFormat('d/m/Y', $request->input('from'))->setTime(0, 0, 0);
        } else {
            $from = null;
        }

        if ($request->has('to')) {
            $to = Carbon::createFromFormat('d/m/Y', $request->input('to'))->setTime(23, 59, 59);
        } else {
            $to = null;
        }

        return [$from, $to];
    }
}
