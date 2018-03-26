<?php

namespace App\Services\Report;


use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportViewer
{
    /**
     * @var Collection
     */
    private $response;

    private $label;

    private $icon;

    private $color;
    /**
     * @var Carbon
     */
    private $from;
    /**
     * @var Carbon
     */
    private $to;

    /**
     * ReportViewer constructor.
     * @param Carbon $from
     * @param Carbon $to
     * @param Collection $response
     * @param $label
     * @param $icon
     * @param $color
     */
    public function __construct(Carbon $from, Carbon $to, Collection $response, $label, $icon, $color)
    {
        $this->from = $from;
        $this->to = $to;
        $this->response = $response;
        $this->label = $label;
        $this->icon = $icon;
        $this->color = $color;
    }

    /**
     * @return ReportResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @return Carbon
     */
    public function getFrom()
    {
        return $this->from->format('d/m/Y');
    }

    /**
     * @return Carbon
     */
    public function getTo()
    {
        return $this->to->format('d/m/Y');
    }
}