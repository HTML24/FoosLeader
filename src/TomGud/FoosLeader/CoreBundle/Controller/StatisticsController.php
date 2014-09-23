<?php
/**
 * Created by PhpStorm.
 * User: Vilius
 * Date: 2014-09-23
 * Time: 10:19
 */

namespace TomGud\FoosLeader\CoreBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StatisticsController  extends Controller {

    public function globalAction() {
        return $this->render('FoosLeaderCoreBundle:Statistics:global.html.twig');
    }

} 