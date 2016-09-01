#!/usr/bin/env python

with open('htaccess_categories') as f:
    for category in f:
        category = category.strip()
        print 'RewriteRule ^{category}.php /category.php?category={category}'.format(
            category=category,
        )
