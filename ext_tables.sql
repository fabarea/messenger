#
# Table structure for table 'tx_messenger_domain_model_sentmessage'
#
CREATE TABLE tx_messenger_domain_model_sentmessage (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	user int(11) DEFAULT '0' NOT NULL,
	message int(11) DEFAULT '0' NOT NULL,
	content varchar(255) DEFAULT '' NOT NULL,
	sent_time int(11) DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);