## Link Check

This is a website monitor that allows other sites to be checked to make sure all links can be followed, both internally and externally.

The scan can be limited to:
- internal pages only.
- a given number of pages (eg, limit to 1000 pages)
- a given depth of scan.  Depth is how many pages you need to go through to find a link to the page

Page throttling can be set system wide in config, and overwritten on a site by site basis.  This limits speed of access to domains, and is should be set to be larger for external pages.  There is only a delay for subsequent calls, and not for the first call, so as long as you don't have multiple pages on the same external domain, you won't see any delays.

To avoid using the site for attacking other domains, a verification code must be added to the site being scanned.  This is then re-validated on each scan to make sure it is still valid.  If multiple entry points are required for separate scans, all codes can be added to one file.

The report can be emailed with only errors/issues directly visible in the email, and a full report attached in a PDF.

#### Installation

Clone from github, set the config (database, email, throttling limits, garbage collection, etc).
Run the migration, and set up a seed to add a user.  They can then reset their password
using the normal Laravel functions for "lost password".

The scans are completed in Queued Jobs within Laravel, so the queue needs to be set up.

#### Commands

**php artisan scan:new --email=[self|owners]**

This runs a new scan, and optionally emails either the user, or the site owners.  Where site owners are emailed, the user is blind copied.

**php artisan gc:scans --age="6 months"**

This deletes older scans from the database.  Age default is set in config.

**php artisan gc:pdf --age="5 days"**

This deletes older pdfs from local storage.  These are automatically regenerated if an email needs to be re-sent, so don't really need to be kept too long on local storage.

## License

This software is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
