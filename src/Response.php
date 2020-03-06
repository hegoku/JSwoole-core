<?php
namespace JSwoole;

class Response
{
    protected $headers=[];
    protected $status_code=200;
    protected $body='';
    protected $redirect_url='';

    public function withHeader(string $name, $value)
    {
        $name=strtolower($name);
        $this->header[$name]=[];
        $this->withAddHeader($name, $value);
        return $this;
    }

    public function withAddHeader(string $name, $value)
    {
        $name=strtolower($name);
        if (!isset($this->headers[$name])) {
            $this->headers[$name]=[];
        }
        if (is_array($value)) {
            $this->headers[$name]=array_merge($this->headers[$name], $value);
        } else{
            array_push($this->headers[$name], $value);
        }
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getHeader($name)
    {
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        } else {
            return [];
        }
    }

    public function getHeaderLine($name)
    {
        if (isset($this->headers[$name])) {
            return implode(',', $this->headers[$name]);
        } else {
            return '';
        }
    }

    public function withStatus($code)
    {
        $this->status_code=$code;
        return $this;
    }

    public function getStatusCode()
    {
        return $this->status_code;
    }

    public function withBody($body)
    {
        $this->body=$body;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function redirect($url)
    {
        $this->withStatus(302);
        $this->redirect_url=$url;
        return $this;
    }

    public function getRedirectUrl()
    {
        return $this->redirect_url;
    }

}