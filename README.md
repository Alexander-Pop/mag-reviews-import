# katai_ekomi
Ekomi (DE) Magento product reviews importer

# Installation
1) Extract the contents to you Magento application ROOT folder
2) Fluch CONFIG -cache
3) Re-login from Magento Backed
4) Visit System > Configuration Katai Extensions > Ekomi Review Importer
5) Modify the configuration to your liking, and Save the configuration.

# Multi-store configuration
1 Make all appropriate changes on Website/Store level.
2) Open the module's config.xml
3) Navigate down to the <crontab> section and add a new cronjob section where the <store_code>[your_code]</store_code> is specified. Make sure that the job-name is unique in your store to not overwrite any existing crons.
4) Deploy the changes + flush configuration

