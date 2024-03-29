CREATE TABLE b_verifier_task (
	ID int(11) NOT NULL AUTO_INCREMENT,
        TASK_DATE datetime DEFAULT NULL,
        STATUS int(11) NOT NULL DEFAULT 0,
	CRM_ENTITY_TYPE int(11) NOT NULL,
        FIELD_USER_OLD char(1) NOT NULL DEFAULT 'N',
	FIELD_NAME_OLD varchar(200) NOT NULL,
	FIELD_NAME_NEW varchar(200) NOT NULL,	
        ITEM_COUNT int(11) NOT NULL,
	PRIMARY KEY (ID)
);

CREATE TABLE b_verifier_task_detail (
	ID int(11) NOT NULL AUTO_INCREMENT,
        TASK_ID int(11) NOT NULL,
        CRM_ENTITY_ID int(11) NOT NULL,
        QUERY varchar(500) NOT NULL,
        STATUS int(11) NOT NULL DEFAULT 0,
        VERIFIER_RESULT char(1) NOT NULL DEFAULT 'N',
	PRIMARY KEY (ID),
        FOREIGN KEY (TASK_ID)
            REFERENCES b_verifier_task (ID)
            ON DELETE CASCADE
);
