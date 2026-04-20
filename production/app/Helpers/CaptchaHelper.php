<?php

use Google\Cloud\RecaptchaEnterprise\V1\Client\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\CreateAssessmentRequest;

if (!function_exists('verifyCaptcha')) {
    function verifyCaptcha(string $token): bool
    {
        $client = new RecaptchaEnterpriseServiceClient();
        $project = env('GOOGLE_CLOUD_PROJECT_ID');
        $siteKey = env('RECAPTCHA_SITE_KEY');

        $event = (new Event())->setSiteKey($siteKey)->setToken($token);
        $assessment = (new Assessment())->setEvent($event);

        $request = (new CreateAssessmentRequest())
            ->setParent($client->projectName($project))
            ->setAssessment($assessment);

        $response = $client->createAssessment($request);

        return $response->getTokenProperties()->getValid();
    }
}