<?php

namespace App\Services;

use Illuminate\Http\Request;
use ReCaptcha\ReCaptcha;
use Carbon\Carbon;

class BladeRunner
{

    /**
     * @var Request $request
     */
    private $request;

    /**
     * BladeRunner constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request  = $request;
    }

    /**
     * Validate CAPTCHA request
     * @return bool
     */
    public function isValidRequest()
    {
        $captcha = $this->request->get("g-recaptcha-response");
        $ip      = $this->request->ip();

        return $this->reCaptchaIsValid($captcha, $ip);
    }

    /**
     * Clear POST requests
     */
    public function clearPostRequests()
    {
        session([
            'anti_robot_posts' => 0
        ]);
    }

    /**
     * Clear others sessions same ip
     */
    public function clearDatabaseSessionsSameIp()
    {
        \DB::table('sessions')->where([
            ['ip_address', '=', $this->request->ip()],
            ['id', '!=', session()->getId()]
        ])->delete();
    }

    /**
     * Check fot request must be validated
     * @return bool
     */
    public function mustValidate()
    {
        return
            $this->countIpInDatabaseLastHours($this->request->ip()) >= config('recaptcha.max_db_entries')
            or
            $this->countPostsRequestsInSession() >= config('recaptcha.max_post_requests');
    }

    /**
     * Increment POST requests in session
     * @param int $increments
     */
    public function incrementPostRequests($increments = 1)
    {
        if ($count = $this->countPostsRequestsInSession()) {
            session([
                'anti_robot_posts' => $count + $increments
            ]);
        } else {
            session([
                'anti_robot_posts' => 1
            ]);
        }
    }

    /**
     * @param string $reCaptchaResponse
     * @param string $ip
     * @return bool
     */
    private function reCaptchaIsValid($reCaptchaResponse, $ip)
    {
        $reCaptcha = new ReCaptcha(config('recaptcha.secret'));
        $response  = $reCaptcha->verify($reCaptchaResponse, $ip);
        return $response->isSuccess();
    }

    /**
     * @return integer
     */
    private function countPostsRequestsInSession()
    {
        return session('anti_robot_posts', 0);
    }

    /**
     * @param string $ip
     * @param int $lastHours
     * @return int
     */
    private function countIpInDatabaseLastHours($ip, $lastHours = 24)
    {
        $timeMinusLastHours = (new Carbon)->now()->subHour($lastHours)->timestamp;
        return \DB::table('sessions')->where([
            ['ip_address','=', "{$ip}"],
            ['last_activity', '>=', "{$timeMinusLastHours}"]
        ])->count();
    }
}
