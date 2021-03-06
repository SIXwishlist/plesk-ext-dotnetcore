<?php

class IndexController extends pm_Controller_Action
{
    /**
     * @var null|pm_Domain Current Domain of the user
     */
    private $domain = null;

    /**
     * init
     *
     * Override to initialize the current user domain once and for all
     */
    public function init() {
        parent::init();
        $this->domain = pm_Session::getCurrentDomain();
    }

    /**
     * indexAction
     */
    public function indexAction() {

        // DEBUG: Paths
        $this->view->paths = [
            'home'    => $this->domain->getHomePath(),
            'docroot' => $this->domain->getDocumentRoot(),
            'vhost'   => $this->domain->getVhostSystemPath()
        ];

        // DEBUG: Test Service File Content Generation
        $service = new Modules_Dotnetcore_Settings_Service([
            'name' => 'app-name-from-settings',
            'entryPoint' => 'EntryPointFromSettings.dll',
            'environment' => 'production',
            'workingDirectory' => $this->domain->getDocumentRoot()
        ]);

        $serviceUser = $this->domain->getSysUserLogin();
        $serviceFileContent = $service->generateServiceFileContent($serviceUser);
        
        $this->view->serviceFileContent = $serviceFileContent;


        // create settings form and handle POST request
        $form = new Modules_Dotnetcore_Settings_Form();
        $request = $this->getRequest();

        if ($request->isPost() && $form->isValid($request->getPost())) {
            // pm_Settings::set('TODO', 'TODO');
            
            $this->_status->addMessage('info', 'Successfully saved');
            $this->_helper->json([
                'redirect' => pm_Context::getBaseUrl()
            ]);
        }

        $this->view->form = $form;
        $this->view->tabs = Modules_Dotnetcore_Common_TabsHelper::getDomainTabs();
        $this->view->pageTitle = pm_Locale::lmsg('pageDomainTitle', [
            'domain' => $this->domain->getName()
        ]);
    }
}
