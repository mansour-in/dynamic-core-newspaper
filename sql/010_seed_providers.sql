USE `core_newspaper`;

INSERT INTO `providers` (slug, name, pattern_type, pattern_template, current_issue, last_issue_url, last_updated_at, cron_status, is_active)
VALUES
('okaz', 'Okaz Newspaper', 'date', 'https://www.okaz.com.sa/digitals/{YYYY}/{MM}/{DD}/index.html', NULL, NULL, NULL, 'pending', 1),
('arabnews', 'Arab News', 'sequence', 'https://www.arabnews.com/sites/default/files/pdf/{ISSUE_ID}/index.html', '50314', 'https://www.arabnews.com/sites/default/files/pdf/50314/index.html', NOW(), 'success', 1),
('ring', 'The Ring Magazine', 'monthly', 'https://ringmagazine.com/en/magazines/{MM_slug}_{YYYY}/view', 'october-2024', 'https://ringmagazine.com/en/magazines/october_2024/view', NOW(), 'success', 1);
