<?php

class remove_duplicates_modx
{

    public $modx = null;
    public $config = array();


    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;

        $corePath = $this->modx->getOption('core_path');
        $assetsPath = $this->modx->getOption('assets');
        $this->config = array_merge(array(
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',
            'controllersPath' => $corePath . 'controllers/',
            'includesPath' => $corePath . 'includes/',
        ), $config);
    }


    /**
     * @return string
     */
    public function initialize(): string
    {

        $initDate = time();
        $this->modx->regClientCSS('https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css');
        $this->modx->regClientCSS(MODX_ASSETS_URL . 'components/remove_duplicates_modx/css/remove_duplicates_modx.css?_='.$initDate);
        $this->modx->regClientStartupScript('https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js');
        $this->modx->regClientStartupScript(MODX_ASSETS_URL . 'components/remove_duplicates_modx/js/remove_duplicates_modx.js?_='.$initDate);

        return '<div id="remove_duplicates_modx_panel" class="container-sm h-100 d-inline-block">
                    <div id="form_panel"></div>
                    <div id="doubles_panel"></div>
                </div>';

    }


    /**
     * @param $params
     * @return array|string[]
     */
    public function getDuplicates($params = array()): array
    {
        $this->checkAdmin();

        $contexts = $this->modx->getCollection('modContext', array('key:NOT IN' => array('mgr')));
        $doubles = array();


        foreach ($contexts as $context) {


            foreach ($this->modx->getIterator('modResource', array('context_key' => $context->key,'deleted'=>0)) as $resource) {


                $countDuplicates = $this->modx->getCount('modResource', array('alias' => $resource->alias, 'context_key' => $context->key,'deleted'=>0));


                if ($countDuplicates > 1) {
                    $idx = 0;
                    $doubles[$resource->context_key][$resource->alias][] = array(
                        "id" => $resource->id,
                        "uri" => $resource->uri,
                        "alias" => $resource->alias,
                        "pagetitle" => $resource->pagetitle,
                    );

                }

            }

        }


        return array('st' => 'ok', 'doubles' => $doubles);
    }


    /**
     * @return array
     */
    public function updateResources()
    {
        $this->checkAdmin();
        $who_is = @$_REQUEST['who_is']; //delete,genAlias
        $resource_id = @$_REQUEST['resource'];
        $iterate = @$_REQUEST['iterate'];
        $iterate_off = @$_REQUEST['iterate_off'];

        $resource = $this->modx->getObject('modResource',$resource_id);
        if ($who_is=="delete"){
            $resource->set('deleted',1);
        } else{
            $alias = $resource->alias;
            $resource->set('alias',$alias."_".$resource->id);
        }

        $resource->save();
        $end = ($iterate_off == $iterate) ? true : false;

        if ($end){
            $this->modx->cacheManager->refresh();
        }
        return array('st' => 'ok','end'=>$end, 'resource' => $resource_id,'who_is'=>$who_is);
    }

    /**
     * @return string[]|void
     */
    private function checkAdmin(){
        if (!$this->modx->user->hasSessionContext('mgr')) return array('st' => 'error', 'msg' => 'access denied');
    }

}