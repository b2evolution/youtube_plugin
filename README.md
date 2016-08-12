# youtube Plugin

Add [YouTube](http://www.youtube.com "Visit YouTube.com to see what it's like") videos to your blog posts without ever leaving the write tab.

## Detailed Description

This plugin allows you to search YouTube, select a video from the results and add it to your post. You don't need to have a YouTube account. You don't need to edit the b2evolution html validator. You don't even need to go to YouTube.com. It's all done from inside your b2evolution backoffice.

## Installation

- Copy the `youtube_plugin` folder into the `plugins` folder of your b2evolution installation.
- Login to the administrative interface for your blog.
- Go to Global Settings::Plugins Install
- Click on Install New
- Find the YouTube plugin in the list and click the "Install" link.

## Plugin Settings

**Thumbnails per page**:
Choose the number of results per page you want to display. The default is 3, which should fit on one row for most monitor resolutions. If you run at a higher resolution or want multiple rows, you can raise this number.

### Usage

Using the plugin is simple.

- From the write tab, click the "YouTube" button below the textarea. This will cause the YouTube toolbar to appear.
- Enter your search term and click "Go". You can enter the tags you want to search by or the YouTube username of the person who uploaded the video you want to find (or both).
- Hover your mouse over the thumbnails that appear to see the title, tags and length. Use the arrows to go to the next page of results.
- Click on a thumbnail to add a video to your post. It will add something to your post like this: `[youtube]vBt3bFboJio[/youtube]`. The renderer portion of the plugin will convert this to the code needed to display the video when your blog post is viewed. Use preview if you want to check the video.
- You can add more than one video if you like. When you're finished adding videos, click on the "X" to hide the YouTube toolbar.

If you know the id of your video and don't want to search for it in the toolbar (or can't find it that way for some reason), you can just add `[youtube]THE_ID[/youtube]` to your post and the renderer will still work.

### Other sites

Although the video search only supports YouTube, you can use this plugin to display videos from other sites, too. Click on the "Other sites" link to get buttons for adding the clips. Click the button for your service, then paste in the id for the clip. You can usually find the id in the url for the video, or in the embed code that the video site provides.

The supported sites are Blip.tv, Current.tv, Daily Motion, Google Video, iFilm, Jumpcut, Revver, vSocial, metacafe and Break.com

Metacafe.com works a little differently than the others. Instead of an id for each video, its embed code has to have an id and a filename. The code in your post will look something like this: `[metacafe]78597/gov_vs_pres_bush.swf[/metacafe]`.