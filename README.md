# General
The *mvcLittleFilesManager* is a php file manager written in php. It fork of
[tinyFilesManager](https://github.com/prasathmani/tinyfilemanager)
Logical code have been entirely re-written to match
with model-view-controller architecture, and have more easier code for work.

With the *mvcLittleFilesManager* you can
- Share files and folders
- Do standard files action, like copy, rename, delete
- Manage files permissions
- Download your files
- Edit text or code files
- Upload files
- Compress and archive files and directory then download it
- View file in different format: Image, Video, pdf, ...
- User access with permissions list

You can not
- Have cloud or file synchronisation
- Use remote Api

What advantage to use the *mvcLittleFilesManager* ?
- It is light. No extra lib more than needed
- It is resource friendly. Fast code. In server side, no framework overload. Code is
  written in pure php
  In client side, no extra javascript taking resources.
- It is secured. As possible, the best security practice have been applied
- Protect life privacy. You choose where your data will be stored. Not sending
  any files in a shadow server thought the world.
- No database requirement.
- Use http client cache
- Fully autonomous.

# Requirement
You need to have:
- php7 or higher, with *json* and *gd*. *Gd* is used for image thumbnail and is
optional.
- apache2 or equivalent.
- a server to host it.
- You **Do not** need a database.

php-json (config)
php-gd (image thumbnail)
No database

# Installation
1. Copy the files and sub-directory in your web hosted home. These files may
   be requested.
	cd ~/www/
	tar -xf mvcLittleFilesManager.tgz
2. Check the .htaccess configuration, in particular the cache control parameters
	editor ~/www/mvcLittleFilesManager/.htaccess
3. In a non-web access directory, create a setting directory in order to store the
   configuration. It is important what this directory is **not accessible** thought
   the http(s) server, because it will store confidential information.
   You could be interested to set up a temporary directory and a data stored
	mkdir ~/mvcLittleFilesManager_data
	mkdir ~/mvcLittleFilesManager_data/share # put your files here
	mkdir ~/mvcLittleFilesManager_data/tmp # if you want onther temporary directory
   directory here
4. Ensure the http-user (*www-data*) have read and write permission on this
   directory. Do a chmod as necessary.
5. Open the configuration files located at src/config.php
	editor ~/www/mvcLittleFilesManager/config.php
5. Setup the *APPDATAPATH* in the first row to the directory previously created
	define('APPDATAPATH', '~/mvcLittleFilesManager_data' . DIRECTORY_SEPARATOR);
6. In default configuration, set up the root directory. This can not be setup
   from root interface to prevent system escalation.
7. The app is now ready to use. Other configurations will be modified in next
   step, by the web interface.

Note that your files *should* not be accessible thought the http server.

# Configuration
With your navigator, pointe to the url related to the server .htaccess
	browser http://localhost/~user/mvcLittleFilesManager

Choose configurations as you need.

####  Application title
This is the title of your application. This will appears on the login page (if
user authentication is selected) and in the top of browse page.

####  Root directory
This is where the root directory live. This option is read-only to avoid setting
a not owned directory. If you need to change it, please edit the file
*config.php*.

#### Timezone
Select the default server timezone. This can be customized by user.

#### Debug mode
If checked, error message will also display the stacktrace of php. This option
is useful in "*developper*" mode, but can share unwanted information for an
attacker. If unsure, set it to false.

#### Date format
The default date format. Could be customized by user.

#### Language
Choose application language. Current language available are en, fr.
More language could be available, but I do not trust google translation.
If you want to your own translation, create a file in trans directory.
In controllers/configurationController.php, update available language.

#### Highlight js style
Change style for code display. Note: this note impact the text editor

#### Max upload size (b)
Define the maximum size of a uploaded file. This value should be in accordance
with the http server setting. Check the ```php_value upload_max_filesize```
option in the .htaccess file.

#### Use authentication
Check this if you want to enabled user authentification system.
See user parapraph for more details.

##### Online viewer
Enabled this option to use google online user. Should not be usefull:
The html5 provide a way to display file on line. Enabled this option may send
the content of your file to a society which anlyse it and shell your personnal
data.
Values could be 'none' (recommanded), 'google', 'microsoft'.

##### Exclude items
If your folder directory contains item to not share on the web, you can
enumerate then here. List all file or directory name concerned, separated with a
comma (',').

#### Show hidden files
if checked, files starting with dot ('.') will be displayed. Elsewhere, they
would be hidden.

#### Remember me timestamp (s)
This options define the time in second of the remember me feature, available on
the login page. Do not take care if Use authentification disabled.

#### Self url
Define the server url. You should not have to use this option unless you encours
difficulty to take the server name and server root path. Defined self url may be
usefull in some (rare) proxy situation.
In doubt, lets it empty.

#### Temporary directory
The the directory used as temporary directory. This will be used for heavy
computation of when a compression is requiered.

#### Thumbnails
Check it to enable image thumnail. It nice but take more space of disk and may
cause problem if you have limited data. The thumbnail directory will be locatted
in the settings directory, next to the config.
This requiere the *gd* php module.

# User
If use authentitication is enabled, users have to login to use the site. This
can be usefull if you want a read-only user in some directory or have a
restircted access.
If authentication enabled and no user exists, a default user with login
***admin*** and password ***admin*** will be created.It  This is not safe, you
have to change this password as son as possible.

The user called **admin** have automatically the full set of permission.

Users description will be do like the bellow.

#### Username
The login and the id of the user. This option is readonly. If you have to change
it, edit the user.json file located under setting directory

#### Password / Confirm Password
Change the password of a user. This password is stored in hah form on
localsystem.

#### Permissions
List of permission associated to a user.
- admin: The user can do everything
- modify: Th user can modify directory, excepted the one listed in
  'exception modify direcory'
- changeperms: user is able to change permisisons of the other user
- showsysinfo: Bottom system information like remaining space or used memory
  will be displayed
- users: The users can list other user and modify them.
- preference: The user is abled to change application setting, even diasabled
  user authentication

#### Root directory
This is the root directory relative to the application folder directory. The
user will be limited to this subdirectory, and can not go up (please warn about
some simlink)
For instance, if folder directory is `/srv/share` and user's root directory
is `/user/private/dir`, the user can navigate only in
`/srv/share/user/private/dir` and their subdirectory

#### Exceptions directories for 'modify'
List here directory name comma separed for which the modify permission is
inverted. If user have access to directory `a,b,c,d` and have not modify
permission, but this field contains `c,d`, then user will be able to write into
c and d directory.

The case occurs if you have well organised directory, you want yours users can
import files but want to keep you repositery ordered. When create a rw
repository next to your project and set it into modify exception field.
The user upload theirs files in this directory. You can check and order then
avoiding jungle created by the human.

#### Language
The langage to translate application for this user.

#### Timezone
The user timeszone. File modifications datetime and creation datetime will be translated
from system local to this user timezone.

##### Is active ?
Check or uncheck this option to enabled or disabled on user. Usefull for
temporary access.

##### Date format
Format to display date to the user.

# Customization
To customize welcome image and favicon of the application, change respectively
the *logo.png* file and the *favicon.ico*.

# Technical consideration
## Repository Structure
Repository structure follow a model-view-controller (MVC) architecture. It is
inspired from the *symfony* structure.
- Code is easier to maintain.
- File are smaller
- Logic and view are separated.

The *controller* repository will contains the page logic. *utils* contains some
helper function. The *view* contains the code displayed to the client.
*exceptions*' have erros templates. Php file in the root is used for routing and
server functionnaly. *trans* contains the translation file.

Configuration file are store and load throught json format.

## Design and translation improvement
Each new text requiere a new translation. Think before adding new text.

New design, more user friendly will be welcome.

# TODO
* remove offline viewer
* add jocker `*` and `**/*` to exlude item option
* self url : should be deleted ?:
* config: thumbnail directory
* compression: avoid DOS
* Disabling user authentfication : only for the admin
* Chek user root directory
* toolstips on setings
* user password expiration
* Unit Tests
