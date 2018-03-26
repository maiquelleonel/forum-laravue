<?php

return [

    /**
     * Tracking status
     */
    'enabled'   => env("TRACK_PAGES", true),

    /**
     * Max 5 trackable variables, example [1 => "offer_id", 2 => "click_id"]
     * !important! Keep array keys
     */
    'custom_vars'   => [
        1 => "a",
        2 => "pubid",
        3 => "s1",
        4 => "s2",
        5 => "s3"
    ],

    /**
     * UTM variables
     */
    'utm_vars' => [
        'utm_source',
        'utm_campaign',
        'utm_medium',
        'utm_term',
        'utm_content',
        'click_id'
    ],

    /**
     * Extra Variables to parse in PostBacks and Pixels
     */
    'extra_vars' => [
        'customer_id',
        'order_id'
    ],

    /**
     * Custom Var Database Prefix
     */
    'custom_var_prefix' => "custom_var_",

    /**
     * Source Vars like ?utm_source=facebook
     */
    'source_vars'   => ['utm_source'],

    /**
     * Campaign Vars like ?utm_campaign=camp-test
     */
    'campaign_vars' => ['utm_campaign'],

    /**
     * Medium Vars like ?utm_medium=cpc
     */
    'medium_vars'   => ['utm_medium'],

    /**
     * Term Vars like ?utm_term=keyword_A
     */
    'term_vars'     => ['utm_term'],

    /**
     * Content Vars like ?utm_content=test_B
     */
    'content_vars'  => ['utm_content'],

    /**
     * Click Id Vars like ?click_id=1bh1k123
     */
    'click_vars'  => ['clickid', 'click_id', 'subid', 'sxid'],

    /**
     * Time in seconds
     */
    'cookie_timelife' => 10800,

    /**
     * Time in seconds
     */
    'eternal_cookie_timelife' => 86400 * 36500,

    /**
     * Session Cookie Name
     */
    'session_cookie_name'   => 'tracking_id',

    /**
     * Eternal Cookie Name
     */
    'eternal_cookie_name'   => 'et_tracking_id',

    /**
     * iframe route
     */
    'iframe_route'  => 'iframe'
];