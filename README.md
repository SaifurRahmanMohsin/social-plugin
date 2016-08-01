# Installation #
After the configuration (see the next heading), you can goto your CMS page, and drop the social login button that you want into the page where you want it to appear and start using it. You can follow the same instructions as RainLab User plugin for other things, including using Session component for the Logout button as the plugin extends over that. Even the social providers can be added the same way as you do with the Account component for the RainLab user plugin. For example,
```
{% if user %}
    <p>Hello {{ user.name }}</p>
{% else %}
    <p>{% component 'facebook' %}</p>
{% endif %}
```

# Configuration #
You need to enable each social network that you would like to use from the backend settings page. You will find this under **Users** tab as **Social login settings**. Enable the particular service and follow the instructions given below for each service you would like to use in your web app. Once configured you can goto your web app's login page and drag the specific Social Login component where you would like the login button to appear.

#### Facebook ####

1. Go to your [Facebook Developers apps page](https://developers.facebook.com/apps) and click on the name of your app. It will take you to dashboard. If it’s a new app then click **Add a new app** and follow the instructions (make sure the test app switch is off). If it asks from platforms, just choose website. Once the app is set up, copy the* App ID* and *App Secret* into the Facebook settings in the backend social login settings page.
2. In your facebook app’s dashboard, under the products section, click Add product and it will take you to Product Setup page. Add **Facebook Login**. This will now show up as a tab under Products section. Click on it and make sure to put the page URL of where you placed the social login button under **Valid OAuth redirect URIs**. For example http://mywebsite.com/login
3. Click Settings (below Dashboard) and then in Basic tab, scroll down and press Add Platform, Click website and verify it has the URL of your website, otherwise fill it with the URL to your website.

#### Google ####
1. Go to [Google Developers Console](https://console.developers.google.com/project) and select your project, otherwise create one using **Create Project**.
2. Once the project is created, goto **API Manager** from the navigation bar and click **Enable API**. Search for the **Google+ API** and add it.
3. Now in the side menu select the **Credentials** and click on **Create Credentials -> OAuth Client ID**. Choose the type as **Web Application** and give it a name such as “For website login”. Set the *Authorized redirect URIs* to your web app's page URL where the Google Login component is to be added and click create. It will show you the *Client ID* and *Client Secret* which should be copied into the Google tab fields in the backend social login settings page.

#### Github ####
1. Go to your [Github's New OAuth Application page](https://github.com/settings/applications) and in the Developer Applications tab, select your project, otherwise create a new application by clicking on **Register a new application**.
2. Set the *Authorization callback URL* to your web app's page URL where the Github Login component is to be added. Once the app is created copy the *Client ID* and *Client Secret* into the Github settings in the backend.

#### LinkedIn ####
1. Go to [Linked’s My Applications page](https://www.linkedin.com/secure/developer) and select your project, otherwise create a new project by clicking on **Create Application**.
2. Make sure that **r_basicprofile**, **r_emailaddress** and **r_contactinfo** permissions are set.
3. Set the *OAuth 2.0 Redirect URLs* to your web app's page URL where the LinkedIn Login component is to be added. Set the same URL for **OAuth 1.0a Accept Redirect URL** and **OAuth 1.0a Cancel Redirect URL** as the component is included code to manage when user accepts / cancels as well. Once the app is created copy the *Client ID* and *Client Secret* into the LinkedIn settings in the backend.

#### Microsoft ####
1. Go to [Microsoft's Application page](https://account.live.com/developers/applications) and select your application under **Live SDK applications**, otherwise create a new application by clicking on **Add an app** and follow the instructions. By default, the web platform would be added.
2. Under web platform, set the *Target Domain* to your web app’s domain and the *Redirect URIs* to the page URL where the Microsoft Login component is to be added. Scroll to the bottom and save it. Copy the **Application Id** as the **Client ID** and **Application Secrets** as the **Client secret** into the Microsoft settings in the backend.

# Errors #
The plugin uses the OAuth 2 specification to perform login. Since this specification is transaction based it's possible that a broken transaction or misconfiguration might raise some Exceptions. These are translated to human readable sentences by the plugin. Some common error messages include

#### Invalid State ####
When a [Cross-Site Request Forgery](https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)) attack is detected this error occurs.

#### This login token has already been used! ####
This happens when a used OAuth token is attempt to be re-used. By default, a token can be used to make a single request i.e. to get the user credentials and generate a new account or link to an existing account.

#### Cancelled by user ####
In order to query the particular provider's API for user details a user is required to authorize the app to allow access to their user details. This error appears when a user fails to authorize the app.

**There are other possible error messages that are generated by the provider's (Facebook, Google, etc.) API and it differs from provider to provider. The plugin outputs these error message in the web app in a human readable format that is provided by the provider itself.**

Like RainLab.User plugin, this plugin also uses the Flash API of OctoberCMS to display errors. You need to add the following lines to  your layout or page in a suitable location for the errors messages to appear.

```
{% flash %}
    <div class="alert alert-{{ type == 'error' ? 'danger' : type }}">{{ message }}</div>
{% endflash %}
```
