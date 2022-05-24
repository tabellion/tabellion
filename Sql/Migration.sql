select count(*) from personne where patronyme not in (select libelle from patronyme);
=> doit être 0 après CreePatronymesManquants.php

alter table personne add `idf_patronyme` mediumint(9) NOT NULL after sexe;

MariaDB [basev4]> alter table personne add `idf_patronyme` mediumint(9) NOT NULL after sexe;
Query OK, 12578256 rows affected (3 min 14.176 sec)
Records: 12578256  Duplicates: 0  Warnings: 0

update personne pe join patronyme pa on (pe.patronyme=pa.libelle) set pe.idf_patronyme=pa.idf

MariaDB [basev4]> update personne pe join patronyme pa on (pe.patronyme=pa.libelle) set pe.idf_patronyme=pa.idf;
Query OK, 12578256 rows affected (8 min 24.964 sec)
Rows matched: 12578256  Changed: 12578256  Warnings: 0

select count(*) from personne where idf_patronyme is null
+----------+
| count(*) |
+----------+
|        0 |
+----------+

alter table personne drop column patronyme;

MariaDB [basev4]> alter table personne drop column patronyme;
Query OK, 12578256 rows affected (2 min 22.514 sec)
Records: 12578256  Duplicates: 0  Warnings: 0

1,2 GO => 893 MO

ALTER TABLE `personne`
  DROP PRIMARY KEY,
   ADD PRIMARY KEY(
     `idf`,
     `idf_patronyme`);
	 
893MO => 933MO

Partitionnement par key sur idf_patronymes (26 partitions) par PhpMyAdmin

ALTER TABLE `personne` PARTITION BY KEY (idf_patronyme) PARTITIONS 26 ( PARTITION p0, PARTITION p1, PARTITION p2, PARTITION p3, PARTITION p4, PARTITION p5, PARTITION p6, PARTITION p7, PARTITION p8, PARTITION p9, PARTITION p10, PARTITION p11, PARTITION p12, PARTITION p13, PARTITION p14, PARTITION p15, PARTITION p16, PARTITION p17, PARTITION p18, PARTITION p19, PARTITION p20, PARTITION p21, PARTITION p22, PARTITION p23, PARTITION p24, PARTITION p25) 

alter table `union` add `idf_patronyme_epx` mediumint(9) NOT NULL after idf_type_acte;

MariaDB [basev4]> alter table `union` add `idf_patronyme_epoux` mediumint(9) NOT NULL after idf_type_acte;
Query OK, 3533080 rows affected (32.046 sec)
Records: 3533080  Duplicates: 0  Warnings: 0

update `union` u join patronyme p on (u.patronyme_epoux=p.libelle) set u.idf_patronyme_epoux=p.idf

MariaDB [basev4]> update `union` u join patronyme p on (u.patronyme_epoux=p.libelle) set u.idf_patronyme_epoux=p.idf;
Query OK, 3533072 rows affected (1 min 22.023 sec)
Rows matched: 3533072  Changed: 3533072  Warnings: 0

select count(*) from `union` where idf_patronyme_epoux is null
+----------+
| count(*) |
+----------+
|        0 |
+----------+

alter table `union` add `idf_patronyme_epouse` mediumint(9) NOT NULL after idf_epoux;
MariaDB [basev4]> alter table `union` add `idf_patronyme_epouse` mediumint(9) NOT NULL after idf_epoux;
Query OK, 3533080 rows affected (38.255 sec)
Records: 3533080  Duplicates: 0  Warnings: 0

update `union` u join patronyme p on (u.patronyme_epouse=p.libelle) set u.idf_patronyme_epouse=p.idf

MariaDB [basev4]> update `union` u join patronyme p on (u.patronyme_epouse=p.libelle) set u.idf_patronyme_epouse=p.idf;
Query OK, 3533077 rows affected (1 min 28.462 sec)
Rows matched: 3533077  Changed: 3533077  Warnings: 0

select count(*) from `union` where idf_patronyme_epouse is null
+----------+
| count(*) |
+----------+
|        0 |
+----------+

alter table `union` drop column patronyme_epoux;

MariaDB [basev4]> alter table `union` drop column patronyme_epoux;
Query OK, 3533080 rows affected (24.306 sec)
Records: 3533080  Duplicates: 0  Warnings: 0

alter table `union` drop column patronyme_epouse;
ariaDB [basev4]> alter table `union` drop column patronyme_epouse;
Query OK, 3533080 rows affected (4.770 sec)
Records: 3533080  Duplicates: 0  Warnings: 0

302,5 MO => 159,9 MO