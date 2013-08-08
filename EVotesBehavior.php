<?php
class EVotesBehavior extends CActiveRecordBehavior
{
	public $selfVoteError;
	public $itemDeleteError;
	public $notRegisteredError = 'Для голосования нужно войти!';
	
	public $itemModelName;
	public $itemFieldName;
	public $item_id;
	
	public $selfVoting = false;
	
	public function getVotes($item_id = null)
	{
		if ($item_id) {
			$this->item_id = $item_id;
		}
		$select = Yii::app()->db->createCommand()
			->select('count(*) as votes')
			->from($this->owner->tableName())
			->where($this->itemFieldName . ' = :item_id AND vote = 1', array(':item_id' => $this->item_id))
			->queryRow()
			;
		$positive = $select['votes'];
		
		$select = Yii::app()->db->createCommand()
			->select('count(*) as votes')
			->from($this->owner->tableName())
			->where($this->itemFieldName . ' = :item_id AND vote = -1', array(':item_id' => $this->item_id))
			->queryRow()
			;
		$negative = $select['votes'];
		
		$votes = array(
			'positive' => $positive,
			'negative' => $negative,
			'rating' => $positive - $negative,
		);
		
		$user = Yii::app()->user;
		if (!$user->isGuest && ($positive || $negative) && ($userVote = $this->_getUserVote())) {
			$votes['userVote'] = $userVote->vote;
		}
		return $votes;
	}
	
	public function addVote($vote, $item_id = null)
	{
		$user = Yii::app()->user;
		if ($user->isGuest) {
			$this->owner->addError($this->itemFieldName, $this->notRegisteredError);
			return false;
		}
		
		if ($item_id) {
			$this->item_id = $item_id;
		}
		if ((!in_array($vote, array(1, -1))) || (!$this->item_id = (int)$this->item_id)) {
			return false;
		}
		$criteria = new CDbCriteria(array(
			'select' => 'user_id',
			'condition' => 'id = :item_id',
			'params' => array(':item_id' => $this->item_id),
		));
		if (!$item = CActiveRecord::model($this->itemModelName)->find($criteria)) {
			$this->owner->addError($this->itemFieldName, $this->itemDeleteError);
			return false;
		}
		
		if (!$this->selfVoting && $item->user_id == $user->id) {
			$this->owner->addError($this->itemFieldName, $this->selfVoteError);
			return false;
		}
		if (($userVote = $this->_getUserVote()) && ($userVote->vote == $vote)) {
			$userVote->delete();
			return true;
		}
		
		if ($userVote) {
			$userVote->delete();
		}
		
		$voteModel = get_class($this->owner);
		$voteModel = new $voteModel;
		$voteModel->{$this->itemFieldName} = $this->item_id;
		$voteModel->user_id = $user->id;
		$voteModel->vote = $vote;
		
		return $voteModel->save();
	}
	
	private function _getUserVote()
	{
		return $this->owner->find('user_id = :user_id AND ' . $this->itemFieldName . ' = :item_id', array(
			':user_id' => Yii::app()->user->id,
			':item_id' => $this->item_id,
		));
	}
}
