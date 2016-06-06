<?php
//CREATE TABLE IF NOT EXISTS `cdr` (
//  `id` char(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
//  `published` int(11) NOT NULL,
//  `answerDuration` int(11) DEFAULT NULL,
//  `source` varchar(255) COLLATE utf8_bin NOT NULL,
//  `destination` varchar(255) COLLATE utf8_bin NOT NULL,
//  `duration` int(11) NOT NULL,
//  `flow` enum('IN','OUT','LOCAL') COLLATE utf8_bin NOT NULL,
//  `result` enum('ANSWERED','BUSY','FAILED','NO_ANSWER','UNKNOWN','NOT_ALLOWED') COLLATE utf8_bin NOT NULL,
//  PRIMARY KEY (`id`)
//) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

class m160403_131101_add_cdr extends CDbMigration
{
	public function up()
	{
        $this->createTable('cdr', array(
          'id' => 'char(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
          'published' => 'int(11) NOT NULL',
          'answerDuration' => 'int(11) DEFAULT NULL',
          'source' => 'varchar(255) COLLATE utf8_bin NOT NULL',
          'destination' => 'varchar(255) COLLATE utf8_bin NOT NULL',
          'duration' => 'int(11) NOT NULL',
          'flow' => "enum('IN','OUT','LOCAL') COLLATE utf8_bin NOT NULL",
          'result' => "enum('ANSWERED','BUSY','FAILED','NO_ANSWER','UNKNOWN','NOT_ALLOWED') COLLATE utf8_bin NOT NULL",
          'PRIMARY KEY (`id`)',
        ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8;');
	}

	public function down()
	{
        $this->dropTable('cdr');
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}