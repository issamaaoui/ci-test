<?php
abstract class Dvl_Avatar_Model_Api2_Avatar_Rest extends Dvl_Avatar_Model_Api2_Avatar
{
    protected function _retrieve()
    {
        $customerId = $this->getApiUser()->getUserId();
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $avatarFullpath = $customer->getAvatarFullpath();
        $data = array(
            'avatar_fullpath' => $avatarFullpath
        );
        return $data;
    }

    protected function _create(array $data)
    {
        if (key_exists('file_content', $data)) {
            if(!empty($data['file_content'])){
                $customerId = $this->getApiUser()->getUserId();
                $customer = Mage::getModel('customer/customer')->load($customerId);
    
                $entry = base64_decode($data['file_content']);
                $imageTmp = imagecreatefromstring($entry);
                if($imageTmp){
                    $helperAvatar = Mage::helper("avatar");
                    
                    
                    $fileNameTmp = $customer->getName() . '-' . $customerId . '.png';
                    $filePathTmp = Mage::getBaseDir('tmp') . DS . $fileNameTmp;
                    header ( 'Content-type:image/png' );
                    imagepng($imageTmp, $filePathTmp);
                    
                    $this->_initFilesystem();
                    $fileName = Varien_File_Uploader::getCorrectFileName($fileNameTmp);
                    $filePath = Varien_File_Uploader::getDispretionPath($fileName);
                    
                    $fileHash = md5(file_get_contents($filePathTmp));
                    $destination = $helperAvatar->getPhotoTargetDir() . $filePath;
                    $this->_createWriteableDir($destination);
                    chmod($destination, 0777);
                    chmod($destination . "/../", 0777);
                    
                    $filePath .= DS . $fileHash . '-' . time() . ".png";
                    $fileFullPath = $helperAvatar->getPhotoTargetDir() . $filePath;
                    
                    
                    $imageObj = new Varien_Image($filePathTmp);
                    $imageObj->constrainOnly(TRUE);
                    $imageObj->keepAspectRatio(TRUE);
                    $imageObj->keepTransparency(TRUE);
                    $imageObj->resize($helperAvatar::AVATAR_HEIGHT);
                    $imageObj->save($fileFullPath);
                    chmod($fileFullPath, 0666);
                    
                    $resultat = str_replace("\\","/",$filePath);
                    $customer->setAvatarSrc($filePath);
                    $avatarFullpath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA, true) . "avatar" . $customer->getAvatarSrc();
                    $customer->setAvatarFullpath($avatarFullpath);
                    $customer->save();
                    
                    echo $this->getRenderer()->render(($this->_retrieve()));
                    die();

                }else{
                    $this->_error('Invalid image format', 201);
                }
            }else{
                $this->_error('Empty content file', 201);
            }
        }else{
            $this->_error('No content file', 201);
        }
    }
    
    public function dispatch()
    {
        switch ($this->getActionType() . $this->getOperation()) {
            /* Create */
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_CREATE:
                // Creation of objects is possible only when working with entity
                $this->_critical(self::RESOURCE_METHOD_NOT_IMPLEMENTED);
                break;
            case self::ACTION_TYPE_ENTITY . self::OPERATION_CREATE:
                // If no of the methods(multi or single) is implemented, request body is not checked
                if (!$this->_checkMethodExist('_create')) {
                    $this->_critical(self::RESOURCE_METHOD_NOT_IMPLEMENTED);
                }
                // If one of the methods(multi or single) is implemented, request body must not be empty
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                // The create action has the dynamic type which depends on data in the request body
                if ($this->getRequest()->isAssocArrayInRequestBody()) {
                    $this->_errorIfMethodNotExist('_create');
                    $filteredData = $this->getFilter()->in($requestData);
                    if (empty($filteredData)) {
                        $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                    }
                    $newItemLocation = $this->_create($filteredData);
                    $this->getResponse()->setHeader('Location', $newItemLocation);
                } else {
                    $this->_errorIfMethodNotExist('_multiCreate');
                    $filteredData = $this->getFilter()->collectionIn($requestData);
                    $this->_multiCreate($filteredData);
                    $this->_render($this->getResponse()->getMessages());
                    $this->getResponse()->setHttpResponseCode(Mage_Api2_Model_Server::HTTP_MULTI_STATUS);
                }
                break;
            /* Retrieve */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_RETRIEVE:
                $this->_errorIfMethodNotExist('_retrieve');
                $retrievedData = $this->_retrieve();
                $filteredData  = $this->getFilter()->out($retrievedData);
                $this->_render($filteredData);
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_RETRIEVE:
                $this->_errorIfMethodNotExist('_retrieveCollection');
                $retrievedData = $this->_retrieveCollection();
                $filteredData  = $this->getFilter()->collectionOut($retrievedData);
                $this->_render($filteredData);
                break;
            /* Update */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_UPDATE:
                $this->_errorIfMethodNotExist('_update');
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                $filteredData = $this->getFilter()->in($requestData);
                if (empty($filteredData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                $this->_update($filteredData);
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_UPDATE:
                $this->_errorIfMethodNotExist('_multiUpdate');
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                $filteredData = $this->getFilter()->collectionIn($requestData);
                $this->_multiUpdate($filteredData);
                $this->_render($this->getResponse()->getMessages());
                $this->getResponse()->setHttpResponseCode(Mage_Api2_Model_Server::HTTP_MULTI_STATUS);
                break;
            /* Delete */
            case self::ACTION_TYPE_ENTITY . self::OPERATION_DELETE:
                $this->_errorIfMethodNotExist('_delete');
                $this->_delete();
                break;
            case self::ACTION_TYPE_COLLECTION . self::OPERATION_DELETE:
                $this->_errorIfMethodNotExist('_multiDelete');
                $requestData = $this->getRequest()->getBodyParams();
                if (empty($requestData)) {
                    $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
                }
                $this->_multiDelete($requestData);
                $this->getResponse()->setHttpResponseCode(Mage_Api2_Model_Server::HTTP_MULTI_STATUS);
                break;
            default:
                $this->_critical(self::RESOURCE_METHOD_NOT_IMPLEMENTED);
                break;
        }
    }
    
    
    /**
     * Directory structure initializing
     */
    protected function _initFilesystem()
    {
        $helperAvatar = Mage::helper('avatar');
        $this->_createWriteableDir($helperAvatar->getTargetDir());
        $this->_createWriteableDir($helperAvatar->getPhotoTargetDir());
    
        // Directory listing and hotlink secure
        $io = new Varien_Io_File();
        $io->cd($helperAvatar->getTargetDir());
        if (!$io->fileExists($helperAvatar->getTargetDir() . DS . '.htaccess')) {
            $io->streamOpen($helperAvatar->getTargetDir() . DS . '.htaccess');
            $io->streamLock(true);
            $io->streamWrite("Order deny,allow\nAllow from all\n");
            $io->streamWrite("Options -Indexes");
            $io->streamUnlock();
            $io->streamClose();
        }
    }
    
    /**
     * Create Writeable directory if it doesn't exist
     *
     * @param string Absolute directory path
     * @return void
     */
    protected function _createWriteableDir($path)
    {
        $io = new Varien_Io_File();
        if (!$io->isWriteable($path) && !$io->mkdir($path, 0777, true)) {
            Mage::throwException(Mage::helper('avatar')->__("Cannot create writeable directory '%s'", $path));
        }
    }
}