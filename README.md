# ServiceTag-Scanner

Thanks: Appnitro pForm (http://www.phpform.org/) for the form template.

Instructions:

[These are a work in progress.  It's 2:10am.  I am tired.]

1) Import the Sample.sql to your database.

	- There are now 2 tables:

		- TagList contains Service Tags, a List (Imported-Bad,Imported-Good,Manually Scanned), Order Number, Found (Yes/No), and Session.
		- Sessions contains SessionID, First Scan, and Last Scan.  This means Sessions are continued no matter what device so long as it's within 30 minutes of Last Scan.

	- Sample Data is included.

2) Update the mysqlConnect.php with your database user information.
	- You'll need to set username, password, database as per mysqli.
3) Import your data into your database.
4) Setup your website so the data is accessible.  Please don't make it web-forward accessible.  It's very hacky and not secured.
5) Attempt to use it

[To Do]

* Build an import front-end so you don't need something like PMA.
* Build a report system.
* Clean up the queries so they're less exploitable
* Make some of the code into function calls to clean up the site.
* More to Come
