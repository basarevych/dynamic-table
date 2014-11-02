Global/Local Configuration Override
===================================

Global Override
---------------
You can use global.php for overriding configuration values from modules, etc.
You would place values in here that are agnostic to the environment and not
sensitive to security.

**NOTE**: In practice, this file will typically be INCLUDED in your source
control, so do not include passwords or other sensitive information in this
file.

Local Override
--------------
These configuration override files are for overriding environment-specific and
security-sensitive configuration information. Copy this file without the
.dist extension at the end and populate values as needed.

**NOTE**: These files are ignored from Git by default with the .gitignore
included. This is a good practice, as it prevents sensitive credentials
from accidentally being committed into version control.
