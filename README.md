EVotesBehavior
======

Расширение для голосования (нравится/не нравится) для Yii-framework.

## Установка ##
1. Скопируйте EVotesBehavior.php в protected/extensions

2. Создайте таблицу для хранения голосов:


CREATE TABLE  `post_vote` (
 `post_id` INT( 10 ) UNSIGNED NOT NULL,
 `user_id` INT( 10 ) UNSIGNED NOT NULL,
 `vote` TINYINT( 4 ) NOT NULL ,
 `date` DATETIME NOT NULL ,
 PRIMARY KEY (  `post_id` ,  `user_id` )
)

И соответствующую под неё модель PostVote. Например,

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
}
```
