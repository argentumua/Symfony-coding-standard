Symfony PHP CodeSniffer Coding Standard
=======================================

How to install
--------------

1. Install PHP_CodeSniffer:

        git clone git://github.com/squizlabs/PHP_CodeSniffer.git /path/to/phpcs
        sudo ln -s /path/to/phpcs/scripts/phpcs /usr/local/bin/phpcs

2. Install Symfony Coding Standard:

        git clone git://github.com/argentumua/Symfony-coding-standard.git /path/to/Symfony
        ln -s /path/to/Symfony /path/to/phpcs/CodeSniffer/Standards

3. Set Symfony as coding standard by default:

        phpcs --config-set default_standard Symfony

How to make a simple test
-------------------------

1. Checking a file (if Symfony is your standard by default)

        phpcs path/to/file.php

2. Checking a file (if Symfony is NOT your standard by default)

        phpcs --standard=Symfony /path/to/file.php

3. Checking a directory (if Symfony is your standard by default)

        phpcs /path/to/directory

4. Checking a directory (if Symfony is NOT your standard by default)

        phpcs --standard=Symfony /path/to/directory

How to use CS in IDE
--------------------

1. NetBeans:

        http://plugins.netbeans.org/plugin/40282/phpmd-php-codesniffer-plugin

2. PHPStorm

        http://www.jetbrains.com/phpstorm/webhelp/using-php-code-sniffer-tool.html

3. Zend Studio

        http://files.zend.com/help/Zend-Studio/content/working_with_php_codesniffer.htm

How to make a standard coding in the team
-----------------------------------------

Use pre-commit hooks, which will make a code standard check for every commit.

1. For Git

        http://git-scm.com/book/en/Customizing-Git-Git-Hooks

2. For SVN

        http://pear.php.net/manual/ru/package.php.php-codesniffer.svn-pre-commit.php
