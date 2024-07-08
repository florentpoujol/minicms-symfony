# this file is aliased to the "dphp" command
# the Symfony console (php bin/console) is aliased to the "dsymfony" command

docker run --rm -v .:/var/minicms-symfony -w /var/minicms-symfony --network minicms-symfony_default minicms-symfony-php:latest $args

# alias the file to a "dphp" (docker php) command:
# https://learn.microsoft.com/en-us/powershell/module/microsoft.powershell.utility/set-alias?view=powershell-7.4
# Set-Alias -Name dphp -value .\docker\php.ps1

# For multiple words alias, we need to set it up as a function first:
# https://learn.microsoft.com/en-us/powershell/module/microsoft.powershell.core/about/about_functions?view=powershell-7.4

# Function DockerSymfonyLocalCli { G:\code\minicms-symfony\docker\php.ps1 php bin/console --ansi $args }
# Function dcomposer { G:\code\minicms-symfony\docker\php.ps1 composer --ansi $args }

# then alias the function, or just give the correct name to the function in the first place
# Set-Alias -Name dsymfony -value DockerSymfonyLocalCli

# -----
# To keep these command after the current terminal session, add them to the bashrc equivalent:
# https://superuser.com/questions/1090141/does-powershell-have-any-sort-of-bashrc-equivalent

# Find the "profile file":
# $PROFILE | Select-Object *

# Create a "profile file" if it doesn't exists:
# New-Item $profile -Type File -Force

# Then edit it:
# notepad {the path to the file}