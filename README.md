EVotesBehavior
======

Расширение для голосования (нравится/не нравится) для Yii-framework.

## Установка ##
1. Скопируйте EVotesBehavior.php в protected/extensions

2. Создайте таблицу для хранения голосов:

```mysql
CREATE TABLE  `post_vote` (
 `post_id` INT( 10 ) UNSIGNED NOT NULL,
 `user_id` INT( 10 ) UNSIGNED NOT NULL,
 `vote` TINYINT( 4 ) NOT NULL ,
 `date` DATETIME NOT NULL ,
 PRIMARY KEY (  `post_id` ,  `user_id` )
)
```

И соответствующую под неё модель PostVote с включением расширения в behaviors. Например,

```php
class PostVote extends ActiveRecord
{
  public function tableName()
	{
		return 'post_vote';
	}
	
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function primaryKey()
	{
		return array('post_id', 'user_id');
	}
	
	public function behaviors()
	{
		return array(
			'vote' => array(
				'class' => 'ext.EVotesBehavior',
				'itemModelName' => 'Post', //Название модели, за материалы которой можно голосовать
				'itemFieldName' => 'post_id', //Внешний ключ в таблице голосов
				'selfVoteError' => 'За свои записи голосовать нельзя!', //Сообщение об ошибке если пользователь голосует за свой материал
				'itemDeleteError' => 'Запись, за которую вы хотите проголосовать, удалена!', //Если материал удалён
				'selfVoting' => false, //Возможность голосовать за свои материалы
			),
		);
	}
}
```
## Использование ##
Пример добавления голоса:
```php
$vote = PostVote::model();
if ($vote->addVote($_POST['vote'], $_POST['post_id'])) {
	$votes = $vote->getVotes();
}
else {
	$errors = $vote->getErrors();
}
```
Пример получения голосов:
```php
$vote = PostVote::model();
$votes = $vote->getVotes($post_id);
```
Метод getVotes возвращает массив со следующими элементами:

positive - количество положительных оценок

negative - количество отрицательных оценок

rating - сумма положительных и отрицательных

userVote - голос текущего пользователя (1 или -1). Если пользователь не оставлял оценок, то null.
