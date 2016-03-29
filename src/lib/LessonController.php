<?php

namespace Pond;


use Slim\Http\Request;
use Slim\Http\Response;

class LessonController extends Controller
{
    private $container;
    private $logger;
  
    function __construct(Container $c ){
		$this->container = $c;
		$this->logger = $this->container->get('logger');
    }  
  
    public function lessonHandler(Request $req, Response $res): Response {
        
    } 
    
}