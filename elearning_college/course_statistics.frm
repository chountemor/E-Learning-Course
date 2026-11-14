TYPE=VIEW
query=select `c`.`id` AS `id`,`c`.`title` AS `title`,`c`.`category` AS `category`,`c`.`price` AS `price`,`c`.`duration` AS `duration`,`c`.`status` AS `status`,count(`l`.`id`) AS `lesson_count` from (`elearning_college`.`courses` `c` left join `elearning_college`.`lessons` `l` on(`l`.`course_id` = `c`.`id`)) group by `c`.`id`,`c`.`title`,`c`.`category`,`c`.`price`,`c`.`duration`,`c`.`status`
md5=ecc335413b89741b5085c0e9215ca982
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001788748631210911
create-version=2
source=SELECT\n    c.id,\n    c.title,\n    c.category,\n    c.price,\n    c.duration,\n    c.status,\n    COUNT(l.id) AS lesson_count\nFROM courses c\nLEFT JOIN lessons l\n    ON l.course_id = c.id\nGROUP BY\n    c.id,\n    c.title,\n    c.category,\n    c.price,\n    c.duration,\n    c.status
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `c`.`id` AS `id`,`c`.`title` AS `title`,`c`.`category` AS `category`,`c`.`price` AS `price`,`c`.`duration` AS `duration`,`c`.`status` AS `status`,count(`l`.`id`) AS `lesson_count` from (`elearning_college`.`courses` `c` left join `elearning_college`.`lessons` `l` on(`l`.`course_id` = `c`.`id`)) group by `c`.`id`,`c`.`title`,`c`.`category`,`c`.`price`,`c`.`duration`,`c`.`status`
mariadb-version=100432
