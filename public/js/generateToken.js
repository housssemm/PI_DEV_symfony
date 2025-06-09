const { RtcTokenBuilder, RtcRole } = require('agora-access-token');

const appId = process.argv[2];
const appCertificate = process.argv[3];
const channelName = process.argv[4];
const uid = parseInt(process.argv[5], 10);
const privilegeExpireTs = parseInt(process.argv[6], 10);

const role = RtcRole.PUBLISHER;

const token = RtcTokenBuilder.buildTokenWithUid(appId, appCertificate, channelName, uid, role, privilegeExpireTs);

console.log(token);