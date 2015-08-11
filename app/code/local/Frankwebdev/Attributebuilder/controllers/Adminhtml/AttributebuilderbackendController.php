<?php
class Frankwebdev_Attributebuilder_Adminhtml_AttributebuilderbackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
      // Mage::log('CONTROLLER');
       $this->loadLayout();
	   $this->_title($this->__("Attribute Builder"));
	   $this->renderLayout();
    }
     private function parseattributes($csv){

     	//clean up string
     	$csvparse = str_replace(" ,", ",", $csv);
     	$csvparse  =str_replace(", ", ",", $csvparse);
     	//convert to array
     	$csvarray = explode(",", $csvparse);

     	return $csvarray;




     }
     public function clearattributeoptionsAction(){
     	$post = $this->getRequest()->getPost();
     	try{
     		if (empty($post)) {
				Mage::throwException($this->__('An error occurred while trying to depete options.'));
			}

			foreach($post as $p=>$q){
				Mage::log($p." ".$q);
				switch($p){
					case "selectattribute":
						$attributecode = $q;
					break;

				}

			}

			$optionsDelete = array();
			$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$attributecode);
			$options = $attribute->getSource()->getAllOptions();
			foreach($options as $option){
				//Mage::log($option['value'][$option_id]);
				$optionsDelete['delete'][$option['value']] = true;
	            $optionsDelete['value'][$option['value']] = true;
			}

			//$attribute->addAttributeOption($optionsDelete);

			$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
			$setup->addAttributeOption($optionsDelete);


			Mage::getSingleton('adminhtml/session')->addSuccess($attribute." options cleared successfully.");
			

     	}catch(Exception $e){
     		Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
     	}

     	$this->_redirect('*/*');

     }

  
     public function saveAction()
    {
    	
    		
    	 $post = $this->getRequest()->getPost();
       

            Mage::log('SAVE FORM DATA');
       try {
			if (empty($post)) {
				Mage::throwException($this->__('Invalid form data.'));
			}
			Mage::log("POST VALID");
			foreach($post as $p=>$q){
				Mage::log($p." ".$q);
				switch($p){
					case "csvvalues":
						$attributeoptions = str_replace('[comma]',',',$this->parseattributes($q));
					break;
					case "selectattribute":
						$attribute = $q;
					break;

				}

			}

			//$arr = $this->checkduplicatesAction($attribute,$attributeoptions)
			//build array from existing attribute 
			$att_arr = array();
			$_attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attribute);
			foreach ($_attribute->getSource()->getAllOptions(true, true) as $instance) {
				$att_arr[] = $instance['label'];
			}	

			//check if the attribute we want to add already exists if it does do not add it
			foreach($attributeoptions as $val){

				foreach($att_arr as $label){
					if($val==$label){
						$key = array_search($val, $attributeoptions);
						unset($attributeoptions[$key]);

					}
				}

			}



			//create array for attribute options
			$i = 0;
			foreach($attributeoptions as $option){
				
					$attribute_model = Mage::getModel('eav/entity_attribute');
					$attribute_code = $attribute_model->getIdByCode('catalog_product',$attribute);
					$_attribute = $attribute_model->load($attribute_code);

					$value['option'] = array($option);
					$order['option'] = $i;
					$result = array('value' => $value, 'order'=>$order);
					Mage::log($result);
					$_attribute->setData('option',$result);
					$_attribute->save();
					$i++;
				
				

			}


			Mage::getSingleton('adminhtml/session')->addSuccess($attribute." updated successfully.");


		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}



    	$this->_redirect('*/*');

    }
}