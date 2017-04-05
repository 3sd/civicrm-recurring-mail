-- /*******************************************************
-- *
-- * civicrm_mailing_recur
-- *
-- * Recurring mailing
-- *
-- *******************************************************/
CREATE TABLE `civicrm_mailing_recur` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  ,
     `mailing_id` int unsigned NOT NULL   COMMENT 'The ID of the Mailing.',
     `recur` text NOT NULL   COMMENT 'Recurrence rule (RECUR).'
,
    PRIMARY KEY ( `id` )

    ,     UNIQUE INDEX `UI_mailing_id`(
        mailing_id
  )

,          CONSTRAINT FK_civicrm_mailing_recur_mailing_id FOREIGN KEY (`mailing_id`) REFERENCES `civicrm_mailing`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_mailing_recurrence
-- *
-- * Instance of a recurring mailing
-- *
-- *******************************************************/
CREATE TABLE `civicrm_mailing_recurrence` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  ,
     `mailing_recur_id` int unsigned NOT NULL   COMMENT 'The ID of the recur rule.',
     `mailing_id` int unsigned NOT NULL   COMMENT 'The ID of the Mailing.'
,
    PRIMARY KEY ( `id` )


,          CONSTRAINT FK_civicrm_mailing_recurrence_mailing_recur_id FOREIGN KEY (`mailing_recur_id`) REFERENCES `civicrm_mailing_recur`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_mailing_recurrence_mailing_id FOREIGN KEY (`mailing_id`) REFERENCES `civicrm_mailing`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;
