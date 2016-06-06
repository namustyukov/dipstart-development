<?php

class UloginUserIdentity implements IUserIdentity
{

    private $id;
    private $name;
    private $isAuthenticated = false;
    private $states = array();

    public function __construct()
    {
    }

    public function authenticate($uloginModel = null, $role = 'Customer')
    {

        $criteria = new CDbCriteria;
        $criteria->condition = 'identity=:identity AND network=:network';
        $criteria->params = array(
            ':identity' => $uloginModel->identity
        , ':network' => $uloginModel->network
        );
        $user = User::model()->find($criteria);

        if (null == $user) {
			$criteria = new CDbCriteria;
			$criteria->condition = 'email=:email';
			$criteria->params = array(
				':email' => $uloginModel->email
			);
			$user = User::model()->find($criteria);
			
            if (null == $user) $user = new User();
			$user->scenario = 'social_network';
            $user->identity = $uloginModel->identity;
            $user->network = $uloginModel->network;
            $user->email = $uloginModel->email;
            $user->full_name = $uloginModel->full_name;
			$user->status = 1;
            $user->save();
			
			$current_role = User::model()->getUserRole($user->id);
			if(!$current_role) {
				if($role != 'Author' && $role != 'Webmaster') $role = 'Customer';
				$AuthAssignment = new AuthAssignment;
				$AuthAssignment->attributes=array('itemname'=>$role,'userid'=>$user->id);
				$AuthAssignment->save();
			}
			/*
			echo 'role: ';
			print_r($current_role);
			Yii::app()->end();*/
		}
		
		$this->id = $user->id;
		$this->name = $user->full_name;
        
        $this->isAuthenticated = true;
        return true;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIsAuthenticated()
    {
        return $this->isAuthenticated;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPersistentStates()
    {
        return $this->states;
    }
}