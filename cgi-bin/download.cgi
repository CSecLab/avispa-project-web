#!/usr/bin/perl

#
# download.cgi - a PERL script which manages downloading the
#   AVISPA Tool from the web.
#
# After the web user fills in a form (http://www.avispa-project.org/download.html),
# downloading the tool happens in three phases:
# 1. a fresh copy of the tool is generated in a randomly-called
#     directory;
# 2. a mail message is sent to the user, with the related link;
# 3. after a specified timeout, the directory is removed, along with
#     the fresh copy of the archive.
#
# Claudio Castellini
# December, 2004
#

# ------------------------------------
# variables
# ------------------------------------

# the page from which the script must have been called upon
my $http_ref1 = "http://www.avispa-project.org/download.html";
my $http_ref2 = "http://avispa-project.org/download.html";
# whereabout is the archive to be downloaded?
my $archive_dir = "/home/ailab/avispa/src/avispa-package/releases/30.06.2006";
my $archive_name_Linux = "avispa-package-1.1_Linux-i686.tgz";
my $archive_name_MAC = "avispa-package-1.1_MacOSX-PPC.tgz";
# the download directory on the website. it must be all-writeable
my $dl_dir = "/home/ailab/www/avispa-project.org/link";
# the prefix of the link to send to the user
my $dl_link = "http://www.avispa-project.org/link";
# how long does the link survive? (seconds)
my $link_lifetime = 600;
# a text file containing the data of each download
my $db_name = "download.db";
# [RC] a text file containing the emails of people that want to be subscribed to
# the AVISPA users mailing list.
my $dbmails_name = "to_be_invited_to_AVISPA_mailinglist.db";

# ------------------------------------
# subroutines
# ------------------------------------

# generate a n-character random string (the name of the directory)
sub generate_random_string
{
	my $length_of_randomstring=shift;

	my @chars=('a'..'z','A'..'Z','0'..'9','_');
	my $random_string;
	foreach ( 1 .. $length_of_randomstring ) {
		$random_string.=$chars[rand @chars];
	}
	return $random_string;
}

# ------------------------------------
# build the array of values entered by the user
# ------------------------------------

$values{sender_ip} = $ENV{REMOTE_ADDR};
$values{sender_host} = $ENV{REMOTE_HOST};
@riga = split(/&/,<STDIN>);
# this little hack transforms CGI text into human-readable text, e.g.,
# changing %20 into spaces, etc.
foreach $riga (@riga) {
	if ( $riga =~ /(.*)=(.*)/ ) {
		$key = $1;
		$value = $2;
		$value =~ s/\+/ /g;
		$value =~ s/%([0-9A-Fa-f]{2})/chr(hex($1))/ge;
		$values{$key} = $value;
	}
}

# ------------------------------------
# sanity checks
# ------------------------------------

# check that the script has been called from the website, in the right way
if ( ($ENV{HTTP_REFERER} ne $http_ref1) && ($ENV{HTTP_REFERER} ne $http_ref2) ) {
	print <<END_OF_TEXT;
Content-type: text/html

<html>

  <head>
    <title>Download error - The AVISPA Project</title>
  </head>

  <body bgcolor=white link=darkred alink=darkred vlink=darkred text=black>

  <h1>ERROR!</h1>

  This page can be invoked only from within the AVISPA Project
  Website.<p>

  Please refer to
  <a href=http://www.avispa-project.org target=_top>the AVISPA Project home page</a>
  from now on.<p>

  </body>

</html>
END_OF_TEXT
	exit;
}

# check that all items entered do have sensible values
my $error = "none";
# 1. mandatory entries must exist
if ( $values{user_name} eq "" ) {
    $error = "field \"User\" cannot be left blank.";
}
if ( $values{inst_name} eq "" ) {
    $error = "field \"Institution\" cannot be left blank.";
}
if ( $values{email} eq "" ) {
    $error = "field \"e-mail\" cannot be left blank.";
}
# 2. email must be sensible
if ( ! ( $values{email} =~ /(.*)@(.*)\.(.*)/ ) ) {
    $error = "e-mail address \"$values{email}\" makes no sense.";
}
# 3. All the input fields have not to be too long;
if ( length($values{user_name}) > 40 ) {
    $error = "field \"User\" too long.";
}
if ( length($values{inst_name}) > 40 ) {
    $error = "field \"Institution\" too long.";
}
if ( length($values{email}) > 40 ) {
    $error = "field \"e-mail\" too long.";
}
if ( length($values{inst_addr}) > 100 ) {
    $error = "field \"Address\" too long.";
}
if ( length($values{comment}) > 200 ) {
    $error = "field \"Further comments\" too long.";
}
if ( length($values{phone}) > 20) {
    $error = "field \"Phone number\" too long.";
}
if ( length($values{purpose}) > 100) {
    $error = "field \"What are you going to use the AVISPA Tool for?\" too long.";
}
# 4. All the input fields have to be sensible;
if ( !($values{user_name} =~ /[\w\s]/ )) {
    $error = "field \"Name\" contains wrong characters.";
}
# gather the result
if ( $error ne "none" ) {
	print <<END_OF_TEXT;
Content-type: text/html

<html>

  <head>
    <title>Download error - The AVISPA Project</title>
  </head>

  <body bgcolor=white link=darkred alink=darkred vlink=darkred text=black>

    <h1>ERROR!</h1>
    
    The data you entered is incomplete and/or incorrect. Error is:<p>

    <font color=red>$error</font><p>
    
    Please try again. Press the "back" button on your browser or click
    <a href=$http_ref1>here</a>
    to get back to the download form.<p>
    
  </body>
    
</html>
END_OF_TEXT
	exit;
}

# ------------------------------------
# update database
# ------------------------------------

# write a new record to the database
$dl_date = `date`; chomp($dl_date);
open (DB,">> $dl_dir/$db_name");
	print DB "\# ------- download at $dl_date\n";
	foreach $key ( keys %values ) {
        	print DB "$key = $values{$key}\n";
	}
close DB;

# [RC] write a new record to the database containing the emails of people that
#      want to be subscribed to the AVISPA users mailing list.
if ( $values{mailing_list} eq "on") {
    open (DBMAILS,">> $dl_dir/$dbmails_name");
    print DBMAILS "$values{email}\n";
    close DBMAILS;
}

# ------------------------------------
# create fresh copy of the archive
# ------------------------------------

# [RC] use Linux or MAC file according to the item entered  
if ( $values{platform} eq "l") {
    $archive_name = $archive_name_Linux;
} else {
    $archive_name = $archive_name_MAC;
}

# pick a name for the new directory
my $random_string = &generate_random_string(10);

# make it
system "mkdir $dl_dir/$random_string";

# make a symbolic link to the package in that random directory
system "ln -s $archive_dir/$archive_name $dl_dir/$random_string/$archive_name";

# copy archive into it
# system "cp $archive_dir/$archive_name $dl_dir/$random_string";
# eventually destroy everything (asynchronously)
# system "( sleep $link_lifetime ; rm -fr $dl_dir/$random_string ) &";
system "( sleep $link_lifetime ; rm -rf $dl_dir/$random_string ) &";

# ------------------------------------
# send a message with the link
# ------------------------------------

open (MAIL,"| mail -s \"The AVISPA Tool download link\" $values{email}");
print MAIL <<END_OF_MESSAGE;
Hello $values{user_name},

this is the AVISPA Project Webmaster at http://www.avispa-project.org.

It appears you have requested the AVISPA Tool for download.

If this is not the case, and this mail was sent to you
by mistake, please ignore this message.

Otherwise, the link below will be available for downloading the
AVISPA Tool for approximately $link_lifetime seconds more:

$dl_link/$random_string/$archive_name

Enjoy the AVISPA Tool,
the AVISPA Webmaster
on behalf of the AVISPA Project Team
END_OF_MESSAGE

close MAIL;

# ------------------------------------
# output a web page with thankyou's
# ------------------------------------

print <<END_OF_TEXT;
Content-type: text/html

<html>

  <head>
    <title>Download - The AVISPA Project</title>
    <style type="text/css" >
     body {font-family : arial,sans-serif;}
    </style>
  </head>

  <body bgcolor=white link=darkred alink=darkred vlink=darkred text=black>

  <center>
  <table cellspacing=0 cellpadding=5 border=0 width="80%">
  <tr>
  <td>
    <center>
      <hr width="40%">
    <h2>The AVISPA tool v. 1.1 download</h2>
      <hr width="40%">
    </center>
  </td>
  </tr>
  <tr>
  <td>

    $values{user_name}, thanks for filling our form in ! <p>

    An e-mail message containing a clickable URI has just been sent to your
    e-mail address. This link will allow you to download the AVISPA Tool.<p>

    <b>The link will automatically expire in $link_lifetime seconds.</b><p>

    Should any problems arise and/or persist with your download, please
    contact the AVISPA webmaster: webmaster at avispa-project dot org.<p>

    Please click <a href=http://www.avispa-project.org target=_top>here</a>
    to get back to the AVISPA Project home page.

  </td>
  </tr>
  </table>

  </body>

</html>
END_OF_TEXT
