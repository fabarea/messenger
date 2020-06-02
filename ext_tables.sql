#
# Table structure for table 'tx_messenger_domain_model_messagetemplate'
#
CREATE TABLE tx_messenger_domain_model_messagetemplate (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	type int(11) DEFAULT '0' NOT NULL,
	source_page int(11) DEFAULT '0' NOT NULL,
	source_file varchar(255) DEFAULT '' NOT NULL,
	qualifier varchar(255) DEFAULT '' NOT NULL,
	subject varchar(255) DEFAULT '' NOT NULL,
	body longtext,
	template_engine varchar(20) DEFAULT '' NOT NULL,
	message_layout int(11) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),

	KEY language (l10n_parent,sys_language_uid)
);

#
# Table structure for table 'tx_messenger_domain_model_messagelayout'
#
CREATE TABLE tx_messenger_domain_model_messagelayout (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	qualifier varchar(255) DEFAULT '' NOT NULL,
	content text,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l10n_parent int(11) DEFAULT '0' NOT NULL,
	l10n_diffsource mediumblob,

	PRIMARY KEY (uid),
	KEY parent (pid),

	KEY language (l10n_parent,sys_language_uid)
);

#
# Table structure for table 'tx_messenger_domain_model_sentmessage'
#
CREATE TABLE tx_messenger_domain_model_sentmessage (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	uuid varchar(36) DEFAULT '' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,

	sender varchar(255) DEFAULT '' NOT NULL,
	recipient varchar(255) DEFAULT '' NOT NULL,
	recipient_cc varchar(255) DEFAULT '' NOT NULL,
	recipient_bcc varchar(255) DEFAULT '' NOT NULL,
	subject varchar(255) DEFAULT '' NOT NULL,
	body longtext,
	attachment text,
	context varchar(255) DEFAULT '' NOT NULL,
	mailing_name varchar(255) DEFAULT '' NOT NULL,
	message_template int(11) unsigned DEFAULT '0' NOT NULL,
	message_layout int(11) unsigned DEFAULT '0' NOT NULL,
	scheduled_distribution_time int(11) unsigned DEFAULT '0' NOT NULL,
	ip varchar(255) DEFAULT '' NOT NULL,
	sent_time int(11) unsigned DEFAULT '0' NOT NULL,
	was_opened int(11) unsigned DEFAULT '0' NOT NULL,
	redirect_email_from varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY uuid (uuid)
);

#
# Table structure for table 'tx_messenger_domain_model_queue'
#
CREATE TABLE tx_messenger_domain_model_queue (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	uuid varchar(36) DEFAULT '' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,

	sender varchar(255) DEFAULT '' NOT NULL,
	recipient varchar(255) DEFAULT '' NOT NULL,
	recipient_cc varchar(255) DEFAULT '' NOT NULL,
	recipient_bcc varchar(255) DEFAULT '' NOT NULL,
	subject varchar(255) DEFAULT '' NOT NULL,
	body longtext,
	attachment text,
	context varchar(255) DEFAULT '' NOT NULL,
	mailing_name varchar(255) DEFAULT '' NOT NULL,
	message_template int(11) unsigned DEFAULT '0' NOT NULL,
	message_layout int(11) unsigned DEFAULT '0' NOT NULL,
	scheduled_distribution_time int(11) unsigned DEFAULT '0' NOT NULL,
	ip varchar(255) DEFAULT '' NOT NULL,
	error_count int(11) unsigned DEFAULT '0' NOT NULL,
	message_serialized mediumtext,
	redirect_email_from varchar(255) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY uuid (uuid)
);
