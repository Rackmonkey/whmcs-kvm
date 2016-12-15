<?php

function deleteCache()
{
     global $templates_compiledir;
     $cacheFiles = glob($templates_compiledir.'*');
     foreach($cacheFiles as $file){
     if(is_file($file) && strpos($file,'index.php') == false)
     unlink($file); // delete file
     }
}
add_hook("ClientAreaPage",1,"deleteCache","");


function hook_provisioningmodule_clientedit(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Consider logging or reporting the error.
    }
}

add_hook('ClientEdit', 1, 'hook_provisioningmodule_clientedit');

add_hook('ClientAreaPrimaryNavbar', 1, function ($menu)
{
    // Check whether the services menu exists.
    /*if (!is_null($menu->getChild('Services'))) {
        // Add a link to the module filter.
        $menu->getChild('Services')
            ->addChild(
                'Provisioning Module Products',
                array(
                    'uri' => 'clientarea.php?action=services&module=marmoset',
                    'order' => 15,
                )
            );
    }*/
});

/*
add_hook('ClientAreaSecondarySidebar', 1, function ($secondarySidebar)
{
    // determine if we are on a page containing My Services Actions
    if (!is_null($secondarySidebar->getChild('My Services Actions'))) {

        // define new sidebar panel
        $customPanel = $secondarySidebar->addChild('Marmoset Panel');

        // set panel attributes
        $customPanel->moveToFront()
            ->setIcon('fa-user')
            ->setBodyHtml(
                'Your HTML output goes here...'
            )
            ->setFooterHtml(
                'Footer HTML can go here...'
            );

        // define link
        $customPanel->addChild(
                'Test Link 1',
                array(
                    'uri' => 'clientarea.php?action=services&module=marmoset',
                    'icon'  => 'fa-list-alt',
                    'order' => 2,
                )
            );

    }
});*/
