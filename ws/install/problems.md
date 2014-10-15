If you are getting error “Too many open files (24)” then your application/command/script 
is hitting max open file limit allowed by linux. You need to increase open file limit as below:

# Increase limit

## Per-User Limit

Open file: /etc/security/limits.conf

Paste following towards end:

*         hard    nofile      500000
*         soft    nofile      500000
root      hard    nofile      500000
root      soft    nofile      500000

500000 is fair number. I am not sure what is max limit but 999999 (Six-9) worked for me once as far as I remember.

Once you save file, you may need to logout and login again.

## System-Wide Limit

Set this higher than user-limit set above.

Open /etc/sysctl.conf 

Add following:

fs.file-max = 2097152

Run: sysctl -p

Above will increase “total” number of files that can remain open system-wide.

## Verify New Limits

Use following command to see max limit of file descriptors:

cat /proc/sys/fs/file-max

### Hard Limit

ulimit -Hn

### Soft Limit

ulimit -Sn
