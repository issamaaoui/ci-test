<?php

class Fidesio_Preorder_Model_Customer extends Mage_Customer_Model_Customer{

    /**
     * Surcharge
     * Elle controle le password avec l'algo SilverStripe et set le password dans Magento
     *
     * @param  string $login
     * @param  string $password
     * @throws Mage_Core_Exception
     * @return true
     */
    public function authenticate_old_______($login, $password)
    {
        $this->loadByEmail($login);
        if ($this->getConfirmation() && $this->isConfirmationRequired()) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('This account is not confirmed.'),
                self::EXCEPTION_EMAIL_NOT_CONFIRMED
            );
        }

        // Check password silverStripe
        if(!$this->getPasswordChecked()){
            $validator = new Fidesio_Preorder_Helper_PasswordEncryptorBlowfish();
            $isValidePassword = $validator->check($this->getSilverstripeCustomerPassword(), $password, $this->getSilverstripeCustomerSalt());

            if($isValidePassword){
                $this->setPassword($password)
                    ->setPasswordChecked(1)
                    ->save();
                // Connecter le user sur silverStripe
                //....
            }
        }


        if (!$this->validatePassword($password)) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Invalid login or password.'),
                self::EXCEPTION_INVALID_EMAIL_OR_PASSWORD
            );
        }
        Mage::dispatchEvent('customer_customer_authenticated', array(
            'model'    => $this,
            'password' => $password,
        ));

        return true;
    }
}