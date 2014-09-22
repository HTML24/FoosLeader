<?php

namespace TomGud\FoosLeader\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FoosLeaderUserBundle extends Bundle
{
	function getParent()
	{
		return 'FOSUserBundle';
	}
}
