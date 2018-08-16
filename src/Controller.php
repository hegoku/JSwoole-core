<?php
namespace JSwoole;

use JSWoole\Request;
use JSwoole\Response;

class Controller
{
    public $request;
    public $response;

    public function __construct(Request $request)
    {
        $this->request=$request;
        $this->response=new Response();
    }

    public function asJson($data)
    {
        $this->response->withHeader('Content-Type', 'application/json')->withStatus(200)->withBody(json_encode($data));
        return $this->response;
    }
}