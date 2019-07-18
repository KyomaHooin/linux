#!/usr/bin/python
#
# Cesnet TCS API client
#
# Import TERENA chain cert:
#
# wget https://pki.cesnet.cz/certs/TERENA_SSL_High_Assurance_CA_3.pem
# openssl x509 -outform der -in TERENA_SSL_High_Assurance_CA_3.pem -out TERENA_SSL_High_Assurance_CA_3.crt
# mv TERENA_SSL_High_Assurance_CA_3.crt /usr/share/local/ca-certificates/
# update-ca-certificates
#

import argparse,httplib,urllib,json,time,ssl,sys

TCS_SERVER='tcs.cesnet.cz'
TCS_REQUEST_URL='/api/v2/certificate/request'
TCS_STATUS_URL='/api/v2/certificate/status'

LOG='cesnet_tcs_tiny.log'

#---------------------------

CLIENT_CRT,CLIENT_KEY,CSR = '','',''

parser = argparse.ArgumentParser(description="Cesnet TCS API tiny client.")
required = parser.add_argument_group('Required arguments:')
required.add_argument("--client-crt", help="Client AUTH certificate.",required=True)
required.add_argument("--client-key", help="Client AUTH key.",required=True)
required.add_argument("--csr", help="Server CSR request.",required=True)
args = parser.parse_args()

log = open(LOG,'a',0)

try:
    with open(args.client-crt,'r') as f: CLIENT_CRT = f.read()
except: log.write("error: Read client certificate.")
try:
    with open(args.client-key,'r') as f: CLIENT_KEY = f.read()
except: log.write("error: Read client key.")
try:
    with open(args.csr,'r') as f: CSR = f.read()
except: log.write("error: Read CSR request.")

TCS_REQUEST_HEADER={'Content-type':'application/json'}
TCS_REQUEST_ID=''

if CLIENT_CRT and CLIENT_KEY and CSR:
    TCS_REQUEST={
        'certificateRequest': CSR,
        'certificateType': 'ov',
        'certificateValidity': 1,
        'notificationMail': 'admin@company.cz',
        'requesterPhone': '+420314159265'
    }

    try:
        context = ssl.SSLContext(ssl.PROTOCOL_TLS)
        context.load_cert_chain(CLIENT_CRT,CLIENT_KEY)
    except:
        log.write("error: SSL context.")

    try:
        c = httplib.HTTPSConnection(TCS_SERVER)
        c.request('POST',TCS_REQUEST_URL,urllib.urlencode(TCS_REQUEST),HEADER)
        r = c.getresponse()
        if r.status == 201:
            data = json.load(r.read())  
            if data['status'] == 'ok': TCS_REQUEST_ID = data['id']
            if data['status'] == 'error':
                log.write("error: " + data['message'])
    except:
        log.write("error: API reqest.")

if TCS_REQUEST_ID:
    while 1:
        try:
            c = httplib.HTTPSConnection(TCS_SERVER)
            c.request('GET',TCS_STATUS_URL + '/' + TCS_REQUEST_ID)
            r = c.getresponse()
            if r.status == 200:
                data = json.load(r.read())  
                if data['status'] in ('refused','revoked','error'):
                    log.write("error: " + data['message'])
                    break
                if data['status'] == 'issued':
                    print data['certificate']
                    break
        except:
            log.write("error: TCS API response.")
        time.sleep(60)

log.close()

