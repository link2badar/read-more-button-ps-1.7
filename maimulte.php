<?php

if (!defined('_PS_VERSION_'))
    exit();

class Maimulte extends Module
{
    public function __construct()
    {
        $this->name = 'maimulte';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Prologue';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.7.1.0', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName      = $this->l('Mai Multe', 'maimulte');
        $this->description      = $this->l('Adăugați butonul Citiți mai multe pe pagina de pornire', 'maimulte');
        $this->confirmUninstall = $this->l('Esti sigur ca vrei sa dezinstalezi?', 'maimulte');
    }

    public function renderView()
    {
        return parent::renderView();
    }

    public function install()
    {
        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);

        return parent::install() &&
            $this->registerHook('displayHome') && Configuration::updateValue('button_text', 'Mai Multe') && Configuration::updateValue('button_link', "https://.......");
    }

    public function uninstall()
    {
        if (!parent::uninstall() || !Configuration::deleteByName('button_text') || !Configuration::deleteByName('button_link'))
            return false;
        return true;
    }

    public function hookDisplayHome($params)
    {
        // < assign variables to template >
        $this->context->smarty->assign(
            array(
                'button_text'       => Configuration::get('button_text'),
                'button_link'       => Configuration::get('button_link')
            )
        );

        return $this->display(__FILE__, 'maimulte.tpl');
    }


    public function displayForm()
    {

        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        // Init Fields form array
        $fields_form = array();
        // < init fields for form array >
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Mai Multe Button'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label'=> $this->l('Text'),
                    'name' => 'button_text',
                    'required' => true
                ),
                array(
                    'type'  => 'text',
                    'label' => $this->l('Legătură'),
                    'name'  => 'button_link',
                    'required' => true
                ),
            ),
            'submit' => array(
                'title' => $this->l('Salvați'),
                'class' => 'btn btn-default pull-right'
            )
        );


        // < load helperForm >
        $helper = new HelperForm();

        // < module, token and currentIndex >
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // < title and toolbar >
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' =>
                array(
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                        '&token='.Tools::getAdminTokenLite('AdminModules'),
                ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to the list')
            )
        );

        // < load current value >
        $helper->fields_value['button_text']    =   Configuration::get('button_text');
        $helper->fields_value['button_link']    =   Configuration::get('button_link');

        return $helper->generateForm($fields_form);
    }


    public function getContent()
    {
        $output = null;

        // < here we check if the form is submited for this module >
        if (Tools::isSubmit('submit'.$this->name)) {
            $button_text         =   strval(Tools::getValue('button_text'));
            $button_link         =   strval(Tools::getValue('button_link'));

            // < make some validation, check if we have something in the input >
            if (!isset($button_text) || !isset($button_link) )
                $output .= $this->displayError($this->l('Vă rugăm să introduceți ceva în câmp.'));
            else
            {
                // < this will update the value of the Configuration variable >
                Configuration::updateValue('button_text', $button_text);
                Configuration::updateValue('button_link', $button_link);

                // < this will display the confirmation message >
                $output .= $this->displayConfirmation($this->l('Formularul este actualizat!'));
            }
        }
        return $output.$this->displayForm();
    }


}
