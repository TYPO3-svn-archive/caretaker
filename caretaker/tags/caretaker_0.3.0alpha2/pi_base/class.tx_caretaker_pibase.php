<?php

abstract class tx_caretaker_pibase extends tslib_pibase {
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
	
		$content=$this->getContent();
		
		//$GLOBALS['TSFE']->additionalHeaderData['caretaker'] = '<link rel="stylesheet" type="text/css" href="'.t3lib_extMgm::siteRelPath('caretaker').'res/css/caretaker.css" />';
		
		return $this->pi_wrapInBaseClass($content);
	}

	abstract function getContent();
	
	function showNodeInfo($node){
		
		$template; // = $this->cObj->cObjGetSingle($this->conf['template'], $this->conf['template.']);
		
		// render first level Children
		if  (is_a($node, 'tx_caretaker_AggregatorNode')){
			
			$template = $this->cObj->cObjGetSingle($this->conf['template'], $this->conf['template.']);
			
			$children  = $node->getChildren();
			$child_template  = $this->cObj->getSubpart($template, '###CARETAKER-CHILD###');
			$child_infos = '';
			foreach ($children as $child){
				$data  = $this->getNodeData($child);
				$lcObj = t3lib_div::makeInstance('tslib_cObj');
				$lcObj->start($data);
				$node_markers = array();
				if ($this->conf['childMarkers.']) {
					foreach (array_keys($this->conf['childMarkers.']) as $key){
						if (  substr($key, -1) != '.'){
							$mark = $lcObj->cObjGetSingle($this->conf['childMarkers.'][$key], $this->conf['childMarkers.'][$key.'.']);
							$node_markers['###'.$key.'###'] = $mark;
						}
					}
					$child_infos .= $this->cObj->substituteMarkerArray($child_template,$node_markers);
				}
			}
			
			$template =  $this->cObj->substituteSubpart($template,'CARETAKER-CHILDREN',$child_infos);
			
			
		} else {
			
			$template = $this->cObj->cObjGetSingle($this->conf['templateChild'], $this->conf['templateChild.']);
			$child_infos = '';
		}
		
			// render Rootline
		$rootline_subpart  = $this->cObj->getSubpart($template, '###ROOTLINE_ITEM###');
		$rootline_items    = array();
		$rootline_node     = $node;
		do{
			$data  = $this->getNodeData($rootline_node);
			$lcObj = t3lib_div::makeInstance('tslib_cObj');
			$lcObj->start($data);
			$node_markers = array();
			if ($this->conf['rootlineMarkers.']) {
				foreach (array_keys($this->conf['rootlineMarkers.']) as $key){
					if (  substr($key, -1) != '.'){
						$mark = $lcObj->cObjGetSingle($this->conf['rootlineMarkers.'][$key], $this->conf['rootlineMarkers.'][$key.'.']);
						$node_markers['###'.$key.'###'] = $mark;
					}
				}
				$rootline_items[] = $this->cObj->substituteMarkerArray($rootline_subpart,$node_markers);
			}
		} while ( $rootline_node = $rootline_node->getParent() );
		
		$rootline_items = array_reverse( $rootline_items );
		$template =  $this->cObj->substituteSubpart($template,'###ROOTLINE###', implode('', $rootline_items) );

			// render Node Infos
		$data  = $this->getNodeData($node);
		$lcObj = t3lib_div::makeInstance('tslib_cObj');
		$lcObj->start($data);
		$node_markers = array();
		if ($this->conf['nodeMarkers.']) {
			foreach (array_keys($this->conf['nodeMarkers.']) as $key){
				if (  substr($key, -1) != '.'){
					$mark = $lcObj->cObjGetSingle($this->conf['nodeMarkers.'][$key], $this->conf['nodeMarkers.'][$key.'.']);
					$node_markers['###'.$key.'###'] = $mark;
				}
			}
			
			$template = $this->cObj->substituteMarkerArray($template,$node_markers);
		}


		return $template;
	}
	
	
	function getNodeData($node){
		$date = array();
			// node data
		$data['uid']           = $node->getUid();
		$data['node_id']       = $node->getCaretakerNodeId();
		$data['node_type']     = $node->getType();
		$data['type']          = $node->getTypeDescription();
		$data['configuration'] = $node->getConfigurationInfo();
		$data['title']         = $node->getTitle();
		$data['description']   = $node->getDescription();

			// add state Infos		
		$result = $node->getTestResult();
		$data['state']       = $result->getState();
		$data['state_info']  = $result->getStateInfo();
		$data['state_show']  = $result->getLocallizedStateInfo();
		$data['state_msg']   = $result->getLocallizedInfotext();
		$data['state_tstamp']= $result->getTstamp();
		
		if(is_a($result, 'tx_caretaker_TestResult')) {
		
			$data['state_value'] = $result->getValue();
		}
		
			// instance data
		if (is_a($node , 'tx_caretaker_TestNode' ) || is_a($node ,'tx_caretaker_TestgroupNode') ) {
			$data['instance'] = $node->getInstance()->getTitle();
		} 
		
		$data['link_parameters'] = '&tx_caretaker_pi_singleview[id]='.$node->getCaretakerNodeId() ;
		return $data;
	}
	
}
	
?>