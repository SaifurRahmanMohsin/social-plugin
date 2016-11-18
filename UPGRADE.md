# Upgrade guide

- [Upgrading to 1.0.5 from 1.0.4](#upgrade-1.0.5)
- [Upgrading to 1.0.6 from 1.0.5](#upgrade-1.0.6)

<a name="upgrade-1.0.5"></a>
## Upgrading To 1.0.5

Check the root folder of your web application. If there are any image files then it may be due to a bug from 1.0.4, you may delete those files as they are not needed. This is a non-destructive upgrade so deleting these files will not remove the images for the existing users that were previously registered.

<a name="upgrade-1.0.6"></a>
## Upgrading To 1.0.6

Go to the plugin folder in your OctoberCMS install at plugin/mohsin/social and delete **composer.lock** and the **vendor** folder. Now perform the plugin upgrade.

If you have already made the upgrade without reading this guide then do the same as above and then additionally perform a **Force Update** of your OctoberCMS project. The force upgrade would pull the new versions of the same vendor folder files that the plugin depends on to function.

If you used SSH to connect, here are the commands for your convenience:
```
cd public_html/plugins/mohsin/social/
rm composer.lock
rm -rf vendor/
```
