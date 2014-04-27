/* 

Setup your database with something like this:
mysqladmin create rbe
echo " grant all on rbe.* to 'rbe'@'localhost' identified by 'CHANGEME'; " | mysql 
echo " flush privileges; " | mysql
mysql rbe < schema.sql

Then edit config-sample.php with appropriate values, and save it as config.php

*/

drop table if exists election;
create table election (
  id mediumint not null auto_increment,
  primary key (id)
) engine = innodb;

drop table if exists candidate;
create table candidate (
  id mediumint not null auto_increment,
  electionid mediumint not null,
  name varchar(64) not null,
  primary key (id),
  constraint candidate_fk_election foreign key (electionid) references election (id) on delete cascade
) engine = innodb;

drop table if exists elector;
create table elector (
  id mediumint not null auto_increment,
  cookie varchar(64) not null,
  primary key (id)
) engine = innodb;

drop table if exists vote;
create table vote (
  id mediumint not null auto_increment,
  electionid mediumint not null,
  electorid mediumint not null,
  rank mediumint not null,
  candidateid mediumint not null,
  primary key (id),
  constraint vote_fk_1 foreign key (electionid) references election (id) on delete cascade,
  constraint vote_fk_2 foreign key (electorid) references elector (id) on delete cascade,
  constraint vote_fk_3 foreign key (candidateid) references candidate (id) on delete cascade
) engine = innodb;
create unique index vote_in1 on vote (electionid,electorid,rank);


