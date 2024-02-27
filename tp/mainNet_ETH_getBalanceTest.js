$(function($) {
    console.log('aaaa');
    // web3 API를 통해 HotWallet, ColdWallet의 잔고를 조회하는 코드입니다
    // web3 모듈
    //const Web3 = require("web3");
    // 메인넷 RPC 설정
    const mainnet = "https://mainnet.infura.io/v3/247ea94e13d54cd9a9a7356255473e3e";
    // web3 객체 선언
    const web3 = new Web3(new Web3.providers.HttpProvider(mainnet));
    // 월렛 주소
    const hotETHAddr = [
        "0xe13896f1fca58db37bde5ac5f39c0b602a99ab31",
        "0xe13896f1fca58db37bde5ac5f39c0b602a99ab31",
    ];
    const coldETHAddr = [
        "0xe13896f1fca58db37bde5ac5f39c0b602a99ab31",
        "0xe13896f1fca58db37bde5ac5f39c0b602a99ab31",
    ];
    // 컨트랙트 주소
    const MCcontract = "address"; // hot1 - mwei
    const TP3contract = "address"; // hot1
    const CTCcontract = "0x00b7db6b4431e345eee5cc23d21e8dbc1d5cada3"; // hot2, cold1 cold2
    const USDTcontract = "address"; // cold1 - mwei

    // contract ABI
    const commonAbi = [
        {
            constant: true,
            inputs: [{ name: "who", type: "address" }],
            name: "balanceOf",
            outputs: [{ name: "", type: "uint256" }],
            type: "function",
        },
    ];

// 이더리움 월렛 잔액 조회 (배열)
    async function getETHbalance(addressArr) {
        let ETHResult = 0;
        for (const address of addressArr) {
            ETHbalance = await web3.eth.getBalance(address);
            ETHResult += parseFloat(web3.utils.fromWei(ETHbalance, "ether"));
        }
        return ETHResult;
    }
// 코인 월렛 잔액 조회
    async function getCoinBalance(contract, walletAddr, fromWeiType) {
        let contractWeb3 = new web3.eth.Contract(commonAbi, contract);
        balance = await contractWeb3.methods.balanceOf(walletAddr).call();
        return web3.utils.fromWei(balance, fromWeiType);
    }
// 코인 월렛 잔액 조회 (배열)
    async function getCoinBalanceArr(contract, walletAddrArr, fromWeiType) {
        let coinResult = 0;
        for (const address of walletAddrArr) {
            let contractWeb3 = new web3.eth.Contract(commonAbi, contract);
            balance = await contractWeb3.methods.balanceOf(address).call();
            coinResult += parseFloat(web3.utils.fromWei(balance, fromWeiType));
        }
        return coinResult;
    }
// 핫월렛
    function hotWallet() {
        getETHbalance(hotETHAddr).then(function (result) {
            // ETH - hotETHAddr 전체 결과값
            try {
                showTotalBalance("ETH", "hot", result);
                console.log("HotWallet ETH balance : " + result + " ETH");
            } catch (err) {
                console.log(err);
            }
        });
        getCoinBalance(MCcontract, hotETHAddr[0], "mwei").then(function (result) {
            // MC
            try {
                console.log("HotWallet1 MC balance : " + result + " MC");
                showTotalBalance("MC", "hot", result);
            } catch (err) {
                console.log(err);
            }
        });
        getCoinBalance(TP3contract, hotETHAddr[0], "ether").then(function (result) {
            // TP3
            try {
                console.log("HotWallet1 TP3 balance : " + result + " TP3");
                showTotalBalance("TP3", "hot", result);
            } catch (err) {
                console.log(err);
            }
        });
        getCoinBalance(CTCcontract, hotETHAddr[1], "ether").then(function (result) {
            // CTC
            try {
                console.log("HotWallet2 CTC balance : " + result + " CTC");
                showTotalBalance("CTC", "hot", result);
            } catch (err) {
                console.log(err);
            }
        });
    }
    /* 콜드 월렛 전체 잔액 조회 */
    function coldWallet() {
        getETHbalance(coldETHAddr).then(function (result) {
            // ETH - coldETHAddr 전체 결과값
            try {
                showTotalBalance("ETH", "cold", result);
                console.log("ColdWallet ETH balance : " + result + " ETH");
            } catch (err) {
                console.log(err);
            }
        });
        getCoinBalance(USDTcontract, coldETHAddr[1], "mwei").then(function (result) {
            // USDT
            try {
                console.log("ColdWallet USDT balance : " + result + " USDT");
                showTotalBalance("USDT", "cold", result);
            } catch (err) {
                console.log(err);
            }
        });
        getCoinBalanceArr(CTCcontract, coldETHAddr, "ether").then(function (result) {
            // CTC 전체 결과값
            try {
                console.log("ColdWallet CTC balance : " + result + " CTC");
                showTotalBalance("CTC", "cold", result);
            } catch (err) {
                console.log(err);
            }
        });
    }
    /* 대시보드에 표시 */
    function showTotalBalance(coinType, walletType, price) {
        //$('#'+walletType+'_wallet_'+coinType).text(numberWithCommas(parseFloat(price).toFixed(2)) + ' '+coinType);
        console.log(walletType, coinType, price);
    }

    hotWallet();
    coldWallet();
    console.log('hi');
});
