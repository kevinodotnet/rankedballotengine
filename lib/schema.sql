/* 

Setup your database with something like this:
mysqladmin create rbe
echo " grant all on rbe.* to 'rbe'@'localhost' identified by 'CHANGEME'; " | mysql 
echo " flush privileges; " | mysql
mysql rbe < schema.sql

Then edit config-sample.php with appropriate values, and save it as config.php

*/

drop table if exists vote;
drop table if exists elector;
drop table if exists candidate;
drop table if exists election;

create table election (
  id mediumint not null auto_increment,
  primary key (id)
) engine = innodb;

create table candidate (
  id mediumint not null auto_increment,
  electionid mediumint not null,
  name varchar(64) not null,
  age tinyint,
  description varchar(256),
  img varchar(256),
  sex varchar(10),
  primary key (id),
  constraint candidate_fk_election foreign key (electionid) references election (id) on delete cascade
) engine = innodb;

create table elector (
  id mediumint not null auto_increment,
  cookie varchar(64) not null,
  primary key (id)
) engine = innodb;

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

/*

Initialization script for Ottawa123 test election.

*/

insert into election (id) values (1);

insert into candidate (electionid,sex,name,age,description,img) values (1,'male','Benny', 31, 'Coin and stamp collecting. Started a petition to ban winter. Favourite Food: Honey.', 'http://ottawa123.ca/sites/all/themes/ottawa123/images/candidates/bear.jpg' );
insert into candidate (electionid,sex,name,age,description,img) values (1,'female','Bev', 41, 'Building houses. Doesn''t like the dentist. Favourite Food: Beavertails.','http://ottawa123.ca/sites/all/themes/ottawa123/images/candidates/beaver.jpg' );
insert into candidate (electionid,sex,name,age,description,img) values (1,'male','Charles', 27, 'Enjoys cycling. Favourite show is House of Cards. Favourite Food: Peanuts, sometimes almonds.','http://ottawa123.ca/sites/all/themes/ottawa123/images/candidates/chipmunk.jpg' );
insert into candidate (electionid,sex,name,age,description,img) values (1,'female','Marie-Eve', 60, 'Loves walking everywhere. Speaks French with an English accent, speaks English with a French accent. Favourite Food: chocolate.','http://ottawa123.ca/sites/all/themes/ottawa123/images/candidates/moose.jpg' );
insert into candidate (electionid,sex,name,age,description,img) values (1,'male','Omar', 33, 'Drinking coffee. Doesn’t sleep very much. Favourite food: Bridgehead coffee.','http://ottawa123.ca/sites/all/themes/ottawa123/images/candidates/owl.jpg' );
insert into candidate (electionid,sex,name,age,description,img) values (1,'female','Peggy', 39, 'Going to Le Nordik to be pampered. Is mostly a vegetarian. Favourite Food: leftovers.','http://ottawa123.ca/sites/all/themes/ottawa123/images/candidates/pig.jpg' );
insert into candidate (electionid,sex,name,age,description,img) values (1,'male','Will', 29, 'Plays the guitar. Volunteers at Bluesfest every year. Favourite Food: Anything with meat.','http://ottawa123.ca/sites/all/themes/ottawa123/images/candidates/wolf.jpg' );


