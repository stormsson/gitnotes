# gitNotes

This web app exposes a POST endpoint where Github (and other systems in the future) can post push events notification.
The app then reads the commits and the files included and checks if the file contains comments to be saved (check the [Annotation](#Annotation) section to see what is saved)

## Configuration

### Google
- Create a new project from the [Google developer console](console.developers.google.com)
- From the **Dashboard** enable the Google Drive API
- From the **Credentials** section create a new set of credentials:
-- Create credentials button > Help me choose > Select "Google Drive API" > Select "Web Server" > Check "Application Data" > Check "No, I'm not using them" > Click the final button.

- Put the downloaded file in **data/google_credentials.json** (create it if you do not have it). Take note of the email included in the credentials.
- From your google drive account create a spreadsheet and share it with the previous noted email. Remember the spreadsheet name
- Alter SPREADSHEET_NAME constant in index.php to match your spreadsheet name

### Github account
- From your account settings > Personal Access Tokens create a new token
- Give to the token full repo scope
- Copy the token in **data/github_personal_access_token** (create it if you do not have it)

### Tracking Github Repositories
- On each repository you want to track, create a webhook that pushes to [yourappdomain]/push the push infomations, in **application/json**  format.

## Annotations
At the moment only the following annotations are saved:
**@noteTitle**
**@noteTags**