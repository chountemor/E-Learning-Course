TYPE=VIEW
query=select `c`.`id` AS `course_id`,`c`.`title` AS `course_title`,count(`e`.`id`) AS `total_students` from (`elearning_college`.`courses` `c` left join `elearning_college`.`enrollments` `e` on(`e`.`course_id` = `c`.`id`)) group by `c`.`id`,`c`.`title`
md5=0b8ac85a12acd841806366aff3e04820
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001788748631244928
create-version=2
source=SELECT\n    c.id AS course_id,\n    c.title AS course_title,\n    COUNT(e.id) AS total_students\nFROM courses c\nLEFT JOIN enrollments e\n    ON e.course_id = c.id\nGROUP BY\n    c.id,\n    c.title
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `c`.`id` AS `course_id`,`c`.`title` AS `course_title`,count(`e`.`id`) AS `total_students` from (`elearning_college`.`courses` `c` left join `elearning_college`.`enrollments` `e` on(`e`.`course_id` = `c`.`id`)) group by `c`.`id`,`c`.`title`
mariadb-version=100432
