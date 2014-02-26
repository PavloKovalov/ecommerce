<?php

/**
 * Filter.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Widgets_Filter_Filter extends Widgets_Abstract
{
    const CACHE_KEY_SETTINGS = 'settings';

    private $_allowedOptions = array(
        'builder'
    );

    protected function _init()
    {
        parent::_init();

        $this->_cachePrefix = strtolower(__CLASS__);

        // disable cache if we are in development environment
        $this->_cacheable = !(APPLICATION_ENV === 'development');

        if ((!empty($this->_options) && $this->_options[0] === 'builder') || (!empty($_SERVER['REQUEST_URI']))) {
            // disabling cache if rendering builder widget
            $this->_cacheable = false;
        }

        $this->_view = new Zend_View();
        $this->_view->setScriptPath(__DIR__ . '/views/');
        $this->_websiteUrl = Zend_Controller_Action_HelperBroker::getStaticHelper('website')->getUrl();
        $this->_view->websiteUrl = $this->_websiteUrl;
    }


    protected function _load()
    {
        if (empty($this->_options)) {
            throw new Exceptions_SeotoasterWidgetException('No options provided. Usage: '
                . implode(', ', $this->_allowedOptions));
        }

        // check if first option is allowed method
        if (in_array($this->_options[0], $this->_allowedOptions)) {
            $method = array_shift($this->_options);
            $method = '_render' . ucfirst(strtolower($method));
            if (method_exists($this, $method)) {
                // render widget
                return $this->$method();
            }
        }

        // render default
        return $this->_renderProduct();
    }

    private function _renderBuilder()
    {
        $layout = Zend_Layout::getMvcInstance()->getView();
        $layout->headScript()
            ->appendFile($this->_websiteUrl . 'system/js/external/underscore/underscore.min.js')
            ->appendFile($this->_websiteUrl . 'system/js/external/backbone/backbone.min.js')
            ->appendFile(
                $this->_websiteUrl . 'plugins/shopping/web/js/modules/filtering/filtering-builder' . (APPLICATION_ENV === 'production' ? '.min' : '') . '.js'
            );

        $mapper = Filtering_Mappers_Eav::getInstance();

        $pageId = intval($this->_toasterOptions['id']);
        $product = Models_Mapper_ProductMapper::getInstance()->findByPageId($pageId);
        if (!$product instanceof Models_Model_Product) {
            throw new Exceptions_SeotoasterWidgetException('This is not a product page');
        }

        $this->_view->productId = $product->getId();
        $this->_view->tags = $product->getTags();
        $this->_view->currentFilters = $mapper->getAttributes($product->getId());


        return $this->_view->render('builder.phtml');
    }

    private function _renderProduct()
    {
        if (empty($this->_options)) {
            throw new Exceptions_SeotoasterWidgetException('Filter widget: at least one tag name should be provided');
        }
        $request = Zend_Controller_Front::getInstance()->getRequest();

        $editMode = Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT);
        if ($editMode) {
            $layout = Zend_Layout::getMvcInstance()->getView();
            $layout->headScript()->appendFile(
                $this->_websiteUrl . 'plugins/shopping/web/js/modules/filtering/filtering-product' . (APPLICATION_ENV === 'production' ? '.min' : '') . '.js'
            );
        }

        $tagsNames = explode(',', $this->_options[0]);
        $tags = Models_Mapper_Tag::getInstance()->findByName($tagsNames, true);

        // generating filter id
        $filterId = implode('_', array_merge(array($this->_toasterOptions['id']), $tagsNames));
        $filterId = substr(md5($filterId), 0, 16);
        $this->_view->filterId = $filterId;

        $this->_view->settings = Filtering_Mappers_FilterSettings::getInstance()->getSettings($filterId);

        $filters = Filtering_Mappers_Eav::getInstance()->findFiltersByTags(array_keys($tags));

        $this->_view->tags = $tagsNames;
        $this->_view->filters = $filters;

        if ($editMode && !$request->has('filter_preview')) {
            return $this->_view->render('filter-product/editor.phtml');
        }

        $this->_view->currentFilters = Filtering_Tools::normalizeFilterQuery();
        return $this->_view->render('filter-product/widget.phtml');
    }
}
