<?php

namespace api\modules\v1;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use yii\web\ForbiddenHttpException;

trait GoogleAccountChecker
{
    protected $url = "https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=%s";

    /**
     * Check if google account is valid from token provided
     *
     * @param $token
     * @param $email
     */
    public function talkToGoogle($token, $email = null)
    {
        $client = new Client();

        $url = sprintf($this->url, $token);

        $request = new Request('GET', $url);

        $promise = $client->sendAsync($request)->then(function (ResponseInterface $response) use ($email) {
            $data = $response->getBody();

            $google_mail = json_decode($data)->email;

            // check if google account is valid
            if ($google_mail === null) {
                throw new ForbiddenHttpException('Invalid Account data');
            }
            // mail check can also be done if necessary
            if ($email !== null) {
                // eeh
                if ($email !== $google_mail) {
                    throw new ForbiddenHttpException("Invalid email provided");
                }
                return true;
            }
            return true;

        }, function (RequestException $e) {

            // nothing much to do
            throw new ForbiddenHttpException(null, 403, $e);
        });

        $promise->wait();
    }
}