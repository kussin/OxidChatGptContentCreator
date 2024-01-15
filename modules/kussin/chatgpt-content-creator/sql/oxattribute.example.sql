SELECT DISTINCT 'oxarticles' AS `object`, oxarticles.OXID AS `object_id`, 'oxattribute' AS `field`, oxarticles.OXSHOPID AS `shop_id`, 0 AS `lang_id`, 'create' AS `mode`, 'pending' AS `status` FROM oxarticles WHERE (oxarticles.OXPARENTID = '') ORDER BY oxarticles.OXTIMESTAMP DESC;