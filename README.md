
GitLab Report for PHP_CodeSniffer
---------------------------------
![Main workflow](https://github.com/micheh/phpcs-gitlab/actions/workflows/main.yml/badge.svg)
[![codecov](https://codecov.io/github/micheh/phpcs-gitlab/graph/badge.svg?token=02FSF3TT0T)](https://codecov.io/github/micheh/phpcs-gitlab)


This library adds a custom report to [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/) (phpcs) to generate a codequality artifact that can be used by GitLab CI/CD.
The custom report is generated in Code Climate format and allows GitLab CI/CD to display the violations in the Code Quality report.

## Installation

Install this library using [Composer](https://getcomposer.org):

```shell script
composer require --dev micheh/phpcs-gitlab
```

Then adjust your `.gitlab-ci.yml` to run PHP_CodeSniffer with the custom reporter and to collect the codequality artifacts:

```yaml
phpcs:
  script: vendor/bin/phpcs --report=full --report-\\Micheh\\PhpCodeSniffer\\Report\\Gitlab=phpcs-quality-report.json
  artifacts:
    reports:
      codequality: phpcs-quality-report.json
```

The example above uses two reports, one to display in the build log (full) and one to generate the codequality artifact file in Code Climate format.

> **Note:** GitLab did not support multiple codequality artifacts before version 15.7. 
> If you are using an earlier version of GitLab, you will not be able to see the violations from multiple tools (e.g. PHP Code Sniffer & PHPStan) in the Code Quality report.

Inside the codequality artifact, GitLab expects relative paths to the files with violations. 
To generate relative paths with PHP Code Sniffer, set the `basepath` argument in your `phpcs.xml.dist` configuration file with `<arg name="basepath" value="."/>` or run phpcs with `--basepath=.` (adjust the base path as needed).

It is also possible to specify the reports to be used in the `phpcs.xml.dist` file:

```xml
<arg name="report" value="full"/>
<arg name="report-\Micheh\PhpCodeSniffer\Report\Gitlab" value="phpcs-quality-report.json"/>
```

## Upgrade from version 1 to 2

The usage of this package remains the same. 
However, the calculation of the fingerprint has been updated and is now based on the content instead of the line number. 
This has the advantage that lines with violations can move up or down and GitLab will not report them as new violations.
When upgrading to version 2, it is likely that all violations will show up as changed once, since all fingerprints are new.

## References

- [PHP_CodeSniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/)
- [GitLab CI/CD Code Quality](https://docs.gitlab.com/ee/ci/testing/code_quality.html)
- [Code Climate Specification](https://github.com/codeclimate/platform/blob/master/spec/analyzers/SPEC.md#data-types)


## License

The files in this archive are licensed under the BSD-3-Clause license.
You can find a copy of this license in [LICENSE.md](LICENSE.md).
