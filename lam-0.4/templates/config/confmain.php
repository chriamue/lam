<?php
/*
$Id$

  This code is part of LDAP Account Manager (http://www.sourceforge.net/projects/lam)
  Copyright (C) 2003  Roland Gruber

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA




*/

include_once ("../../lib/config.inc");

// start session
session_save_path("../../sess");
@session_start();

setlanguage();

// check if button was pressed and if we have to save the setting or go back to login
if ($_POST['back'] || $_POST['submitconf']){
	// save settings
	if ($_POST['submitconf']){
		// save HTTP-POST variables in session
		$_SESSION['conf_passwd'] = $_POST['passwd'];
		$_SESSION['conf_passwd1'] = $_POST['passwd1'];
		$_SESSION['conf_passwd2'] = $_POST['passwd2'];
		$_SESSION['conf_serverurl'] = $_POST['serverurl'];
		$_SESSION['conf_cachetimeout'] = $_POST['cachetimeout'];
		$_SESSION['conf_admins'] = $_POST['admins'];
		$_SESSION['conf_suffusers'] = $_POST['suffusers'];
		$_SESSION['conf_suffgroups'] = $_POST['suffgroups'];
		$_SESSION['conf_suffhosts'] = $_POST['suffhosts'];
		$_SESSION['conf_suffdomains'] = $_POST['suffdomains'];
		$_SESSION['conf_minUID'] = $_POST['minUID'];
		$_SESSION['conf_maxUID'] = $_POST['maxUID'];
		$_SESSION['conf_minGID'] = $_POST['minGID'];
		$_SESSION['conf_maxGID'] = $_POST['maxGID'];
		$_SESSION['conf_minMach'] = $_POST['minMach'];
		$_SESSION['conf_maxMach'] = $_POST['maxMach'];
		$_SESSION['conf_usrlstattr'] = $_POST['usrlstattr'];
		$_SESSION['conf_grplstattr'] = $_POST['grplstattr'];
		$_SESSION['conf_hstlstattr'] = $_POST['hstlstattr'];
		$_SESSION['conf_maxlistentries'] = $_POST['maxlistentries'];
		$_SESSION['conf_lang'] = $_POST['lang'];
		$_SESSION['conf_samba3'] = $_POST['samba3'];
		$_SESSION['conf_pwdhash'] = $_POST['pwdhash'];
		$_SESSION['conf_scriptpath'] = $_POST['scriptpath'];
		$_SESSION['conf_scriptserver'] = $_POST['scriptserver'];
		$_SESSION['conf_pdf_usertext'] = $_POST['pdf_usertext'];
		$_SESSION['conf_filename'] = $_POST['filename'];
		metaRefresh("confsave.php");
	}
	// back to login
	else if ($_POST['back']){
		metaRefresh("../login.php");
	}
	exit;
}

// get password if register_globals is off
if ($_POST['passwd']) $passwd = $_POST['passwd'];

// check if password was entered
// if not: load login page
if (! $passwd) {
	$message = _("No password was entered!");
	require('conflogin.php');
	exit;
}

// check if password is valid
// if not: load login page
include_once ('../../lib/config.inc');
$conf = new Config($_POST['filename']);
if (!(($conf->get_Passwd()) == $passwd)) {
	$message = _("The password is invalid! Please try again.");
	require('conflogin.php');
	exit;
}

echo $_SESSION['header'];

echo ("<title>" . _("LDAP Account Manager Configuration") . "</title>\n");
echo ("<link rel=\"stylesheet\" type=\"text/css\" href=\"../../style/layout.css\">\n");
echo ("</head>\n");
echo ("<body>\n");
echo ("<p align=\"center\"><a href=\"http://lam.sf.net\" target=\"new_window\">".
	"<img src=\"../../graphics/banner.jpg\" border=1 alt=\"LDAP Account Manager\"></a></p>\n<hr>\n<p></p>\n");

// display formular
echo ("<form action=\"confmain.php\" method=\"post\">\n");

echo ("<fieldset><legend><b>" . _("Server settings") . "</b></legend>");
echo ("<table border=0>");
// serverURL
echo ("<tr><td align=\"right\"><b>" . _("Server address") . " *: </b></td>".
	"<td align=\"left\">".
	"<input size=50 type=\"text\" name=\"serverurl\" value=\"" . $conf->get_ServerURL() . "\">".
	"</td>\n");
echo ("<td><a href=\"../help.php?HelpNumber=201\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");

// new line
echo ("<tr><td colspan=3>&nbsp</td></tr>");

// user suffix
echo ("<tr><td align=\"right\"><b>".
	_("UserSuffix") . " *: </b></td>".
	"<td><input size=50 type=\"text\" name=\"suffusers\" value=\"" . $conf->get_UserSuffix() . "\"></td>\n");
echo ("<td><a href=\"../help.php?HelpNumber=202\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");
// group suffix
echo ("<tr><td align=\"right\"><b>".
	_("GroupSuffix") . " *: </b></td>".
	"<td><input size=50 type=\"text\" name=\"suffgroups\" value=\"" . $conf->get_GroupSuffix() . "\"></td>\n");
echo ("<td><a href=\"../help.php?HelpNumber=202\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");
// host suffix
echo ("<tr><td align=\"right\"><b>".
	_("HostSuffix") . " *: </b></td>".
	"<td><input size=50 type=\"text\" name=\"suffhosts\" value=\"" . $conf->get_HostSuffix() . "\"></td>\n");
echo ("<td><a href=\"../help.php?HelpNumber=202\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");
// domain suffix
echo ("<tr><td align=\"right\"><b>".
	_("DomainSuffix") . " **: </b></td>".
	"<td><input size=50 type=\"text\" name=\"suffdomains\" value=\"" . $conf->get_DomainSuffix() . "\"></td>\n");
echo ("<td><a href=\"../help.php?HelpNumber=202\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");

// new line
echo ("<tr><td colspan=3>&nbsp</td></tr>");

// LDAP password hash type
echo ("<tr><td align=\"right\"><b>".
	_("Password hash type") . " *: </b></td>".
	"<td><select name=\"pwdhash\">\n<option selected>" . $conf->get_pwdhash() . "</option>\n");
if ($conf->get_pwdhash() != "CRYPT") echo("<option>CRYPT</option>\n");
if ($conf->get_pwdhash() != "SHA") echo("<option>SHA</option>\n");
if ($conf->get_pwdhash() != "SSHA") echo("<option>SSHA</option>\n");
if ($conf->get_pwdhash() != "MD5") echo("<option>MD5</option>\n");
if ($conf->get_pwdhash() != "SMD5") echo("<option>SMD5</option>\n");
if ($conf->get_pwdhash() != "PLAIN") echo("<option>PLAIN</option>\n");
echo ("</select></td>\n");
echo ("<td><a href=\"../help.php?HelpNumber=215\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");

// new line
echo ("<tr><td colspan=3>&nbsp</td></tr>");

// LDAP cache timeout
echo ("<tr><td align=\"right\"><b>".
	_("Cache timeout") . " *: </b></td>".
	"<td><select name=\"cachetimeout\">\n<option selected>".$conf->get_cacheTimeout()."</option>\n");
if ($conf->get_cacheTimeout() != 0) echo("<option>0</option>\n");
if ($conf->get_cacheTimeout() != 1) echo("<option>1</option>\n");
if ($conf->get_cacheTimeout() != 2) echo("<option>2</option>\n");
if ($conf->get_cacheTimeout() != 5) echo("<option>5</option>\n");
if ($conf->get_cacheTimeout() != 10) echo("<option>10</option>\n");
if ($conf->get_cacheTimeout() != 15) echo("<option>15</option>\n");
echo ("</select></td>\n");
echo ("<td><a href=\"../help.php?HelpNumber=214\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");

echo ("</table>");
echo ("</fieldset>");
echo ("<p></p>");

echo ("<fieldset><legend><b>" . _("Samba settings") . "</b></legend>");
echo ("<table border=0>");

// Samba version
echo ("<tr><td align=\"right\"><b>".
	_("Samba 3.x schema") . ": </b></td><td><select name=\"samba3\">\n");
if ($conf->is_samba3()) echo ("<option>yes</option><option>no</option></select></td>");
else echo ("<option>no</option><option>yes</option></select></td>");
echo ("<td><a href=\"../help.php?HelpNumber=213\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");

echo ("</table>");
echo ("</fieldset>");
echo ("<p></p>");

echo ("<fieldset><legend><b>" . _("Ranges") . "</b></legend>");
echo ("<table border=0>");

// minUID
echo ("<tr><td align=\"right\"><b>".
	_("Minimum UID number") . " *: </b>".
	"<input size=6 type=\"text\" name=\"minUID\" value=\"" . $conf->get_minUID() . "\"></td>\n");
// maxUID
echo ("<td align=\"right\"><b>" . _("Maximum UID number") . " *: </b>".
	"<input size=6 type=\"text\" name=\"maxUID\" value=\"" . $conf->get_maxUID() . "\"></td>\n");
// UID text
echo ("<td><a href=\"../help.php?HelpNumber=203\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");
// minGID
echo ("<tr><td align=\"right\"><b>".
	_("Minimum GID number") . " *: </b>".
	"<input size=6 type=\"text\" name=\"minGID\" value=\"" . $conf->get_minGID() . "\"></td>\n");
// maxGID
echo ("<td align=\"right\"><b>" . _("Maximum GID number")." *: </b>".
	"<input size=6 type=\"text\" name=\"maxGID\" value=\"" . $conf->get_maxGID() . "\"></td>\n");
// GID text
echo ("<td><a href=\"../help.php?HelpNumber=204\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");
// minMach
echo ("<tr><td align=\"right\"><b>".
	_("Minimum Machine number") . " *: </b>".
	"<input size=6 type=\"text\" name=\"minMach\" value=\"" . $conf->get_minMachine() . "\"></td>\n");
// maxMach
echo ("<td align=\"right\"><b>" . _("Maximum Machine number") . " *: </b>".
	"<input size=6 type=\"text\" name=\"maxMach\" value=\"" . $conf->get_maxMachine() . "\"></td>\n");
// Machine text
echo ("<td><a href=\"../help.php?HelpNumber=205\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");

echo ("</table>\n");
echo ("</fieldset>\n");
echo ("<p></p>\n");

echo ("<fieldset><legend><b>" . _("LDAP List settings") . "</b></legend>\n");
echo ("<table border=0>\n");

// user list attributes
echo ("<tr><td align=\"right\"><b>".
	_("Attributes in User List") . " *:</b></td>".
	"<td><input size=50 type=\"text\" name=\"usrlstattr\" value=\"" . $conf->get_userlistAttributes() . "\"></td>");
echo ("<td><a href=\"../help.php?HelpNumber=206\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");
// group list attributes
echo ("<tr><td align=\"right\"><b>".
	_("Attributes in Group List") . " *:</b></td>".
	"<td><input size=50 type=\"text\" name=\"grplstattr\" value=\"" . $conf->get_grouplistAttributes() . "\"></td>");
echo ("<td><a href=\"../help.php?HelpNumber=206\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");
// host list attributes
echo ("<tr><td align=\"right\"><b>".
	_("Attributes in Host List") . " *:</b></td>".
	"<td><input size=50 type=\"text\" name=\"hstlstattr\" value=\"" . $conf->get_hostlistAttributes() . "\"></td>");
echo ("<td><a href=\"../help.php?HelpNumber=206\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");

echo ("<tr><td colspan=3>&nbsp</td></tr>\n");

// maximum list entries
echo ("<tr><td align=\"right\"><b>".
	_("Maximum list entries") . " *: </b></td>".
	"<td><select name=\"maxlistentries\">\n<option selected>".$conf->get_MaxListEntries()."</option>\n");
if ($conf->get_MaxListEntries() != 10) echo("<option>10</option>\n");
if ($conf->get_MaxListEntries() != 20) echo("<option>20</option>\n");
if ($conf->get_MaxListEntries() != 30) echo("<option>30</option>\n");
if ($conf->get_MaxListEntries() != 50) echo("<option>50</option>\n");
if ($conf->get_MaxListEntries() != 75) echo("<option>75</option>\n");
if ($conf->get_MaxListEntries() != 100) echo("<option>100</option>\n");
echo ("</select></td>\n");
echo ("<td><a href=\"../help.php?HelpNumber=208\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");

echo ("</table>\n");
echo ("</fieldset>\n");
echo ("<p></p>\n");

echo ("<fieldset><legend><b>" . _("Language settings") . "</b></legend>\n");
echo ("<table border=0>\n");

// language
echo ("<tr>");
echo ("<td><b>" . _("Default language") . ":</b></td><td>\n");
// read available languages
$languagefile = "../../config/language";
if(is_file($languagefile))
{
	$file = fopen($languagefile, "r");
	$i = 0;
	while(!feof($file))
	{
		$line = fgets($file, 1024);
		if($line == "\n" || $line[0] == "#" || $line == "") continue; // ignore comment and empty lines
		$languages[$i] = chop($line);
		$i++;
	}
	fclose($file);
// generate language list
echo ("<select name=\"lang\">");
for ($i = 0; $i < sizeof($languages); $i++) {
	$entry = explode(":", $languages[$i]);
	if ($conf->get_defaultLanguage() != $languages[$i]) echo("<option value=\"" . $languages[$i] . "\">" . $entry[2] . "</option>\n");
	else echo("<option selected value=\"" . $languages[$i] . "\">" . $entry[2] . "</option>\n");
}
echo ("</select>\n");
}
else
{
	echo _("Unable to load available languages. Setting English as default language. For further instructions please contact the Admin of this site.");
}
echo ("</td>\n");
echo ("<td><a href=\"../help.php?HelpNumber=209\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");

echo ("</table>\n");
echo ("</fieldset>\n");

echo ("<p></p>\n");

// script settings
echo ("<fieldset><legend><b>" . _("Script settings") . "</b></legend>\n");
echo ("<table border=0>\n");

echo ("<tr><td align=\"right\"><b>".
	_("Server of external script") . ": </b></td>".
	"<td><input size=50 type=\"text\" name=\"scriptserver\" value=\"" . $conf->get_scriptServer() . "\"></td>\n");
echo ("<td><a href=\"../help.php?HelpNumber=211\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");
echo ("<tr><td align=\"right\"><b>".
	_("Path to external script") . ": </b></td>".
	"<td><input size=50 type=\"text\" name=\"scriptpath\" value=\"" . $conf->get_scriptPath() . "\"></td>\n");
echo ("<td><a href=\"../help.php?HelpNumber=210\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");

echo ("</table>\n");
echo ("</fieldset>\n");

echo ("<p></p>\n");

// PDF settings
echo ("<fieldset><legend><b>" . _("PDF settings") . "</b></legend>\n");
echo ("<table border=0>\n");

echo ("<tr><td align=\"right\"><b>".
	_("Text for user PDF") . ": </b></td>".
	"<td><textarea name=\"pdf_usertext\" cols=\"80\" rows=\"5\">" . $conf->get_pdftext() . "</textarea></td>\n");
echo ("<td><a href=\"../help.php?HelpNumber=216\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");

echo ("</table>\n");
echo ("</fieldset>\n");

echo ("<p></p>\n");

// security setings
echo ("<fieldset><legend><b>" . _("Security settings") . "</b></legend>\n");
echo ("<table border=0>\n");
// admin list
echo ("<tr><td align=\"right\"><b>".
	_("List of valid users") . " *: </b></td>".
	"<td><input size=50 type=\"text\" name=\"admins\" value=\"" . $conf->get_Adminstring() . "\"></td>\n");
echo ("<td><a href=\"../help.php?HelpNumber=207\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");

echo ("<tr><td colspan=3>&nbsp;</td></tr>\n");

// new password
echo ("<tr><td align=\"right\"><font color=\"red\"><b>".
	_("New Password") . ": </b></font></td>".
	"<td align=\"left\"><input type=\"password\" name=\"passwd1\"></td>\n");
echo ("<td rowspan=2><a href=\"../help.php?HelpNumber=212\" target=\"lamhelp\">" . _("Help") . "</a></td></tr>\n");
// reenter password
echo ("<tr><td align=\"right\"><font color=\"red\"><b>".
	_("Reenter Password") . ": </b></font></td>".
	"<td align=\"left\"><input type=\"password\" name=\"passwd2\"></td></tr>\n");
echo ("</table>\n");
echo ("</fieldset>\n");
echo ("<p></p>\n");


// buttons
echo ("<table border=0>\n");

echo ("<tr><td align=\"left\"><pre>".
	"<input type=\"submit\" name=\"submitconf\" value=\"" . _("Submit") . "\">".
	"<input type=\"reset\" name=\"resetconf\" value=\"" . _("Reset") . "\">".
	"<input type=\"submit\" name=\"back\" value=\"" . _("Abort") . "\"\n");

echo ("></pre></td></tr>\n");

echo ("</table>\n");

echo ("<p></p>");

echo ("<p>* = ". _("required") . "</p>");
echo ("<p>** = ". _("required for Samba 3 schema") . "</p>");

// password for configuration
echo ("<p><input type=\"hidden\" name=\"passwd\" value=\"" . $passwd . "\"></p>\n");

// config file
echo ("<p><input type=\"hidden\" name=\"filename\" value=\"" . $_POST['filename'] . "\"></p>\n");

echo ("</form>\n");
echo ("</body>\n");
echo ("</html>\n");

?>
