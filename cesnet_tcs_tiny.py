#!/usr/bin/python
#
# Cesnet TCS APIclient
#
# Import TERENA chain cert.
#
# wget https://pki.cesnet.cz/certs/TERENA_SSL_High_Assurance_CA_3.pem
# openssl x509 -outform der -in TERENA_SSL_High_Assurance_CA_3.pem -out TERENA_SSL_High_Assurance_CA_3.crt
# mv TERENA_SSL_High_Assurance_CA_3.crt /usr/share/local/ca-certificates/
# update-ca-certificates
#

import httplib,urllib,json,time,ssl,sys

HEADER={'Content-type':'application/json'}

TCS_SERVER='tcs.cesnet.cz'
TCS_STATUS_URL='/api/v2/certificate/status/'
TCS_REQUEST_URL='/api/v2/certificate/request'

#--------------------------

print sys.argv

#required.add_argument("--client-crt", help="Client AUTH certificate.",required=True)
#required.add_argument("--client-key", help="Client AUTH key.",required=True)
#required.add_argument("--csr", help="Server CSR request.",required=True)
#args = parser.parse_args()

#try:
#    with open(args.client-crt,'r') as f: CLIENT_CRT = f.read()
#except:
#    print "Failed to load client AUTH certificate."
#    sys.exit(1)

TCS_REQUEST={
    'certificateRequest': '',
    'certificateType': 'ev',
    'certificateValidity': 2,
    'notificationMail': 'admin@company.cz',
    'requesterPhone': '+420314159265'
}


context = ssl.SSLContext(ssl.PROTOCOL_TLS)
#context.load_cert_chain(CLIENT_CRT,CLIENT_KEY)

try:
    c = httplib.HTTPSConnection(TCS_SERVER,timeout=10)
    c.request('POST',TCS_REQUEST_URL,urllib.urlencode(TCS_REQUEST),HEADER)
    r = c.getresponse()
    print r.read()
    if r.status == 201:
        data = json.load(r.read())  
        if data['status'] == 'ok':
            TCS_REQUEST_ID = data['id']
        if data['status'] == 'error':
            print "error: ", data['message']
except:
    print "Reqest error."

if True:
    while 1:
        try:
            c = httplib.HTTPSConnection(TCS_SERVER,timeout=10)
            c.request('GET',TCS_STATUS_URL + '',HADER)
            r = c.getresponse()
            if r.status == 200:
                data = json.load(r.read())  
                if data['status'] in ('added','accepted'): pass
                if data['status'] in ('refused','revoked','error'):
                   print "error: ", data['message']
                if data['status'] == 'issued':
                    update_cert(data['certificate'])
                    break
        except:
                print "Response error."
        time.sleep(60)

