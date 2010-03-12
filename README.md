LG Addon Updater - Never miss an addon update again!
====================================================

LG Addon Updater is an [ExpressionEngine][ee] extension that allows 3rd party developers to notify site administrators of addon version updates via an alert on the CP homepage.

If you're a developer adding an update is easy. First register your addon with a couple of simple hooks and provide an XML file wit update information. LG Addon Update will handle the rest.

Overview
--------

### Screenshots

![Extension settings](http://s3.amazonaws.com/ember/HOXDtfTUU9OLyqPHcu0QPGd5IVWAumOd_m.jpg)  
Extension settings

![CP homepage notifications](http://s3.amazonaws.com/ember/OhcM6hUSagGq9vZgpH3qORDiiuioCevF_m.jpg)  
Extension settings   

Getting started
-------------

### Requirements

LG Addon Updater requires ExpressionEngine 1.6+ but is not available for EE 2.0+ yet. Addon update notifications require [LG Addon Updater][lg_addon_updater].

Technical requirements include:

* PHP5
* A modern browser: [Firefox][firefox], [Safari][safari], [Google Chrome][chrome] or IE8+

Other requirements:

LG Addon Updater requires the 'Morphine' default CP theme addon. [Download the addon from Github][gh_morphine_theme].

### Installation

1. Download the latest version of LG Addon Updater
2. Extract the .zip file to your desktop
3. Copy `system/extensions/ext.lg_addon_updater_ext.php` to `/system/extensions/`
4. Copy `system/language/english/lang.lg_addon_updater_ext.php` to `/system/languages/english`

### Activation

1. Open the [Extension Manager][ee_extensions_manager]
2. Enable Extensions if not already enabled
3. Enable the extension
4. Configure the extension settings

### Configuration

LG Addon Updater has the following extension settings which need to be entered separately for each Multi-Site Manager site.

Note: All configuration options are site specific. When a new site is created be sure to save the extension settings for the new site to avoid errors.

#### Update notification preferences

For those extensions that allow it, LG Addon Updater checks the 3rd paty developers site to see if their installed addons (plugins, extensions & modules) have been updated. Results are cached for a set period of time.

#### Would you like LG Addon Updater to check for developer updates and display them on your CP homepage?

Enable / disable new 3rd party addon version notifications.

#### How many minutes you like the update check cached for?

To avoid CP processing LG Addon Updater caches responses from 3rd party developer sites. This setting determines how often requests are sent to 3rd party developers.

#### Check for LG Addon Updater updates?

LG Addon Updater can call home, check for recent updates and display them on your CP homepage? This feature requires [LG Addon Updater][lg_addon_updater] to be installed and activated.

##### Would you like this extension to check for updates?

Enable / disable new LG Addon Updater version notifications.

User guide
----------

LG Addon Updater is not designed to be implemented by site administrators or end users. It is a developers extension written to facilitate easy delivery of update notifications when a 3rd party addon is updated.

To include a new addon in the update check you must first register a source file and addon id using LG Addon Updater hooks.

#### Creating a source file

A source file is just a simple XML file that lists all a developers addons with version numbers, last updated UNIX timestamp and documentation url. It looks like this:

    <versions>
        <addon id='LG Addon Updater' version='1.0.1' last_updated='1219175483' docs_url='http://leevigraham.com/cms-customisation/expressionengine/lg-addon-updater/' />
        <addon id='LG Add Sitename' version='1.2.2' last_updated='1219175483' docs_url='http://leevigraham.com/cms-customisation/expressionengine/lg-add-sitename/' />
        <addon id='LG Better Meta Commercial' version='1.6.2' last_updated='1219175483' docs_url='http://leevigraham.com/cms-customisation/expressionengine/lg-better-meta/' />
        <addon id='LG Member List' version='1.3.3' last_updated='1219175483' docs_url='http://leevigraham.com/cms-customisation/expressionengine/lg-member-list/' />
        <addon id='LG Member Form Customiser' version='1.2.1' last_updated='1219175483' docs_url='http://leevigraham.com/cms-customisation/expressionengine/lg-member-form-customiser/' />
        <addon id='LG Multi Language' version='1.0.2' last_updated='1219175483' docs_url='http://leevigraham.com/cms-customisation/expressionengine/lg-multi-language/' />
        <addon id='LG Polls' version='1.1.0' last_updated='1219175483' docs_url='http://leevigraham.com/cms-customisation/expressionengine/lg-polls/' />
        <addon id='LG Social Bookmarks' version='2.0.1' last_updated='1219175483' docs_url='http://leevigraham.com/cms-customisation/expressionengine/lg-social-bookmarks/' />
        <addon id='LG TinyMCE' version='1.3.3' last_updated='1219175483' docs_url='http://leevigraham.com/cms-customisation/expressionengine/lg-tinymce/' />
        <addon id='LG Twitter' version='2.0.1' last_updated='1219175483' docs_url='http://leevigraham.com/cms-customisation/expressionengine/lg-twitter/' />
    </versions>

The source file must contain a root `<versions>` node and at least one `<addon>` child node.

The `<addon>` node must have the following attributes:

##### id

     id='LG Addon Updater'

A unique id for your addon. This id is the same used in the `lg_addon_update_register_addon` hook.

##### version

    version='1.0.1'

The current version of the addon.

##### last_updated

    last_updated='1219175483'

The time the addon was last updated in UTC as a UNIX timestamp. Why a UNIX timestamp? Well its the best way to ensure that all users see a correct localised time.

##### docs_url

    docs_url='http://leevigraham.com/cms-customisation/expressionengine/lg-addon-updater/'

The URL where the addon documentation and download is available.

### Registering your addon using LG Addon Updater hooks

LG Addon uses ExpressionEngine's extension hook system to make registering an addon super easy. The two new hooks provided are  `lg_addon_update_register_source` and `lg_addon_update_register_addon`.

#### `lg_addon_update_register_source`

This hook registers a new source file. This hook returns data so make sure you check if anyone else is using the hook using `$EXT->last_call`.

An example of the function called by the `lg_addon_update_register_source` hook:

    /**
     * Register a new Addon Source
     *
     * @param    array $sources The existing sources
     * @return   array The new source list
     * @since version 1.0.0
     */
    function register_my_addon_source($sources)
    {
        global $EXT;
        
        if($EXT->last_call !== FALSE)
            $sources = $EXT->last_call;
            
        if($this->settings['check_for_extension_updates'] == 'y')
            $sources[] = 'http://leevigraham.com/version-check/versions.xml';
            
        return $sources;
    }

#### `lg_addon_update_register_addon`

This hook registers the 3rd party addon.  This hook returns data so make sure you check if anyone else is using the hook using `$EXT->last_call`.

An example of the function called by the `lg_addon_update_register_addon` hook:

    /**
     * Register a new Addon
     *
     * @param    array $addons The existing sources
     * @return   array The new addon list
     * @since    Version 1.0.0
     */
    function register_my_addon_id($addons)
    {
        global $EXT;
    
        if($EXT->last_call !== FALSE)
            $addons = $EXT->last_call;
        
        if($this->settings['check_for_extension_updates'] == 'y')
            $addons[LG_AU_addon_id] = $this->version;
        
        return $addons;
    }

#### More Examples?

The best example of how to register your addon is to look at the LG Addon extension source code. The extension actually registers itself to check for updates!

Release Notes
-------------

### Upgrading?

* Before upgrading back up your database and site first, you can never be too careful.
* Never upgrade a live site, you're asking for trouble regardless of the addon.
* After an upgrade if you are experiencing errors re-save the extension settings for each site in your ExpressionEngine install.

There are no specific upgrade notes for this version.

### Change log

#### 1.1.2

* Fixed settings bug

#### 1.1.1

* Rewrote documentation
* LG Addon Updater now requires the 'Morphine' default CP theme addon. [Download the theme addon from Github][gh_morphine_theme].


#### 1.0.2

* PHP4 fixes or should I say downgrade?

#### 1.0.1

* Added CP homepage check to reduce object initialisation and hook method calling; basically a speed improvement.

#### 1.0.0

Initial Release

Support
-------

Technical support is available primarily through the [ExpressionEngine forums][ee_forums]. [Leevi Graham][lg] and [Newism][newism] do not provide direct phone support. No representations or guarantees are made regarding the response time in which support questions are answered.

Although we are actively developing this addon, [Leevi Graham][lg] and [Newism][newism] makes no guarantees that it will be upgraded within any specific timeframe.

License
------

Ownership of this software always remains property of the author.

You may:

* Modify the software for your own projects
* Use the software on personal and commercial projects

You may not:

* Resell or redistribute the software in any form or manner without permission of the author
* Remove the license / copyright / author credits

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

[lg]: http://leevigraham.com
[newism]: http://newism.com.au

[ee]: http://expressionengine.com/index.php?affiliate=newism
[ee_forums]: http://expressionengine.com/index.php?affiliate=newism&page=forums
[ee_extensions_manager]: http://expressionengine.com/index.php?affiliate=newism&page=docs/cp/admin/utilities/extension_manager.html

[firefox]: http://firefox.com
[safari]: http://www.apple.com/safari/download/
[chrome]: http://www.google.com/chrome/

[lg_addon_updater]: http://leevigraham.com/cms-customisation/expressionengine/lg-addon-updater/
[gh_morphine_theme]: http://github.com/newism/nsm.morphine.theme
