%.html: %.hlpsl ../../src/hlpsl2latex/hlpsl2latex
	hlpsl2latex -html $< >$@

###########################################################
#
# all

default: html

PROTOCOLS = \
../../docs/ProtocolsRep/2.2-AAAMobileIP/AAAMobileIP.html \
../../docs/ProtocolsRep/2.17-seamoby/CTP-non_predictive-fix.html \
../../docs/ProtocolsRep/3.15-SIP/sip.html \
../../docs/ProtocolsRep/3.14-H.530/h.530.html \
../../docs/ProtocolsRep/3.14-H.530/h.530-fix.html \
../../docs/ProtocolsRep/2.19-NSIS/QoS-NSLP.html \
../../docs/ProtocolsRep/2.18-Geopriv/geopriv.html \
../../docs/ProtocolsRep/2.18-Geopriv/self-signatures.html \
../../docs/ProtocolsRep/2.18-Geopriv/two_pseudonyms.html \
../../docs/ProtocolsRep/2.18-Geopriv/pervasive.html \
../../docs/ProtocolsRep/2.18-Geopriv/password.html \
../../docs/ProtocolsRep/3.12-IMPP/Simple.html \
../../docs/ProtocolsRep/4.3-SPKM-LIPKEY/lipkey-spkm-known-initiator.html \
../../docs/ProtocolsRep/4.3-SPKM-LIPKEY/lipkey-spkm-unknown-initiator.html \
../../docs/ProtocolsRep/1.4-ChapV2/CHAPv2.html \
../../docs/ProtocolsRep/1.9-APOP/apop.html \
../../docs/ProtocolsRep/2.10-CRAM-MD5/CRAM-MD5.html \
../../docs/ProtocolsRep/2.13-DHCP-Delayed-Auth/DHCP-delayed-auth.html \
../../docs/ProtocolsRep/2.16-DNSsec/DNSSEC.html \
../../docs/ProtocolsRep/1.10-TSIG/TSIG.html \
../../docs/ProtocolsRep/3.10-EAP/EAP_AKA.html \
../../docs/ProtocolsRep/3.10-EAP/EAP_Archie.html \
../../docs/ProtocolsRep/3.10-EAP/EAP_IKEv2.html \
../../docs/ProtocolsRep/3.10-EAP/EAP_SIM.html \
../../docs/ProtocolsRep/3.10-EAP/EAP_TLS.html \
../../docs/ProtocolsRep/3.10-EAP/EAP_TTLS_CHAP.html \
../../docs/ProtocolsRep/3.10-EAP/PEAP.html \
../../docs/ProtocolsRep/3.9-SKEY/skey.html \
../../docs/ProtocolsRep/1.5-EKE/EKE.html \
../../docs/ProtocolsRep/1.5-EKE/EKE2.html \
../../docs/ProtocolsRep/1.5-EKE/SPEKE.html \
../../docs/ProtocolsRep/1.6-SRP/SRP.html \
../../docs/ProtocolsRep/2.3-IKEv2Main/IKEv2-DS.html \
../../docs/ProtocolsRep/2.3-IKEv2Main/IKEv2-DSx.html \
../../docs/ProtocolsRep/2.3-IKEv2Main/IKEv2-MAC.html \
../../docs/ProtocolsRep/2.3-IKEv2Main/IKEv2-MACx.html \
../../docs/ProtocolsRep/2.3-IKEv2Main/IKEv2-CHILD.html \
../../docs/ProtocolsRep/3.11-IKEv2-EAP/IKEv2-EAP-Archie.html \
../../docs/ProtocolsRep/2.15-Radius/RADIUS-RFC2865.html \
../../docs/ProtocolsRep/2.15-Radius/8021x_Radius.html \
../../docs/ProtocolsRep/2.8-HIP/hip.html \
../../docs/ProtocolsRep/2.11-PBK/PBK.html \
../../docs/ProtocolsRep/2.11-PBK/PBK-fix.html \
../../docs/ProtocolsRep/2.11-PBK/PBK-fix-weak-auth.html \
../../docs/ProtocolsRep/2.7-Kerberos/Kerb-basic.html \
../../docs/ProtocolsRep/2.7-Kerberos/Kerb-Ticket-Cache.html \
../../docs/ProtocolsRep/2.7-Kerberos/Kerb-Cross-Realm.html \
../../docs/ProtocolsRep/2.7-Kerberos/Kerb-Forwardable.html \
../../docs/ProtocolsRep/2.7-Kerberos/Kerb-PKINIT.html \
../../docs/ProtocolsRep/2.7-Kerberos/Kerb-preauth.html \
../../docs/ProtocolsRep/3.1-TESLA/tesla.html \
../../docs/ProtocolsRep/3.5-SSH/ssh-transport.html \
../../docs/ProtocolsRep/2.12-TSP/tsp.html \
../../docs/ProtocolsRep/2.6-TLS/TLS.html \
../../docs/ProtocolsRep/2.1-ASW/ASW.html \
../../docs/ProtocolsRep/4.2-FairZG/FairZG.html \
../../docs/ProtocolsRep/4.1-SET/SET-purchase.html \
../../docs/ProtocolsRep/4.1-SET/SET-purchase-honest-payment-gateway.html \
../../docs/ProtocolsRep/1.2-UMTS-AKA/UMTS_AKA.html \
../../docs/ProtocolsRep/1.3-ISO-PK/ISO1.html \
../../docs/ProtocolsRep/1.3-ISO-PK/ISO2.html \
../../docs/ProtocolsRep/1.3-ISO-PK/ISO3.html \
../../docs/ProtocolsRep/1.3-ISO-PK/ISO4.html \
../../docs/ProtocolsRep/2.4-2pRSA/2pRSA.html \
../../docs/ProtocolsRep/2.9-MutAuthLPD/LPD-MSR.html \
../../docs/ProtocolsRep/2.9-MutAuthLPD/LPD-IMSR.html \
../../docs/ProtocolsRep/1.1-SHARE/SHARE.html \
../../docs/ProtocolsRep/0.1-NSPK/NSPKxor.html \
../../docs/ProtocolsRep/0.1-NSPK/NSPK.html \
../../docs/ProtocolsRep/0.1-NSPK/NSPK-fix.html \
../../docs/ProtocolsRep/0.1-NSPK/NSPK-KS.html \
../../docs/ProtocolsRep/0.1-NSPK/NSPK-KS-fix.html \


html: $(PROTOCOLS)
	cp ../../docs/ProtocolsRep/*/*.html .

clean:
	rm ../../docs/ProtocolsRep/*/*.html 

