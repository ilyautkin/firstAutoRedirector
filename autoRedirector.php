<?php
// ==============
// autoRedirector
// ==============

// Plugin for MODX REVO for automatic redirect if uri was changed


$context = "redirector";
$uri = $resource->uri;
$old_uri = $_SESSION[$id."-uri"];
$redirectorParams = array(
         "show_in_tree" => 0
       , "published"    => 1
       , "uri_override" => 1
       , "uri"          => $uri
       , "context_key"  => $context
    );

switch ($modx->event->name) {
  case "OnBeforeDocFormSave":
    if ($mode == 'upd') {
        $_SESSION[$id."-uri"] = $uri;
    }
    break;
  case "OnDocFormSave":
    if ($mode == 'upd') {
        if ($old_uri == $uri) {break;}
          else {
            if ($redirector = $modx->getObject("modResource", $redirectorParams)) {
                $redirector->set("uri",$old_uri);
                $redirector->save();
                break;
            }
         }
        $redirectorParams["uri"] = $old_uri;
        $redirectorParams["longtitle"] = $id;
        $response = $modx->runProcessor('resource/create', $redirectorParams);
        $redirectorId = $response->response['object']['id'];
        $redirector = $modx->getObject("modResource", $redirectorId);
        $redirector->set("pagetitle", $redirectorId);
        $redirector->set("alias",     $redirectorId);
        $redirector->save();
        break;
    }
    break;

  case "OnPageNotFound":
    $url = $_SERVER['REQUEST_URI'];
    $uri = str_replace($modx->getOption("site_url"),"",$url);
    if (substr($uri, 0, 1) == "/") $uri = substr($uri, 1);
    $redirector = $modx->getObject('modResource', array("uri" => $uri));
    if ($redirector) $modx->sendRedirect($modx->makeUrl($redirector->get('longtitle')));
    
    if (substr($url, -5) == ".html") $url = substr($url, 0, -5);
    if (substr($url, -1) == "/")     $url = substr($url, 0, -1);
    $url = str_replace($modx->getOption("site_url"),"",$url);
    $url_array = explode('/', $url);
    $alias = array_pop($url_array);
    $res = $modx->getObject('modResource', array("alias" => $alias));
    if ($res) $modx->sendRedirect($modx->makeUrl($res->get('id')));
    break;
}
