#!/bin/bash

# ==                == #
# == Static analyze == #
# ==                == #

# Full static analyzer, works only with AST - slow and good.
# (developed by Etsy)
./vendor/bin/phan \
    --directory ./src \
    --directory ./vendor \
    --exclude-directory-list ./vendor \
    --analyze-twice \
    --dead-code-detection \
    --unused-variable-detection \
    --redundant-condition-detection

# There is also:
# - PHPStan (https://phpstan.org/)
# - Psalm (https://github.com/vimeo/psalm)
# - Exakat (https://exakat.readthedocs.io/en/latest/Introduction.html)

# ==                       == #
# == cyclomatic complexity == #
# ==                       == #
./vendor/bin/churn run src
##
## https://thevaluable.dev/code-quality-check-tools-php/
##
# - phpmd (PHP mess detector) (https://phpmd.org/rules/codesize.html)
# - phpdepend (https://github.com/pdepend/pdepend)
# - phpmetrics (https://phpmetrics.org/)
# - phploc (https://github.com/sebastianbergmann/phploc)
# - phpinsights (https://github.com/nunomaduro/phpinsights)
# - churn-php (https://github.com/bmitch/churn-php)
