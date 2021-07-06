<?php

namespace App\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Crypto;
use App\Services\Post;

class BinanceService extends Command
{
    # get all crypto
    public static function getAllCrypto()
    {
        # ALL CRYPTO: {{binance}}/api/v3/exchangeInfo
        try {
            $response = Http::get(env('BINANCE_API')."/api/v3/exchangeInfo");
        }
        # connection error
        catch(\GuzzleHttp\Exception\ConnectException $e) {
            Log::debug('Connection error', $e);
            return false;
        }
        # bad response error
        catch(\GuzzleHttp\Exception\BadResponseException $e) {
            Log::debug('Response error', $e);
            return false;
        }
        # request error
        catch(\GuzzleHttp\Exception\RequestException $e) {
            Log::debug('Request error', $e);
            return false;
        }

        # decode json response
        $json_response = json_decode($response->body(), true);

        # set response
        $response = [];

        # loop all crypto
        foreach($json_response["symbols"] as $crypto)
        {
            # symbol contains ***USDT 
            # symbol not contains ***DOWN 
            # symbol not contains ***UP
            if(strrpos($crypto["symbol"], "USDT") && !strrpos($crypto["symbol"], "DOWN", -3) && (!strrpos($crypto["symbol"], "UP", -2) && $crypto["symbol"] != "UP"))
            {
                # set cryopto name
                $name = str_replace("USDT", "", $crypto["symbol"]);

                # add crypto
                $response = [
                    "symbol" => $crypto["symbol"],
                    "name" => str_replace("USDT", "", $crypto["symbol"]),
                ];

                # add current crypto to good crypto
                Crypto::updateOrCreate($response, []);
            }
        }

        # return response
        return true;
    }

    # get current average price
    public static function getPrice($name = "")
    {
        # CURRENT AVERAGE PRICE: {{binance}}/api/v3/avgPrice?symbol={{ATAUSDT}}
        try {
            $response = Http::get(env('BINANCE_API')."/api/v3/avgPrice?symbol=$name");
        }
        # connection error
        catch(\GuzzleHttp\Exception\ConnectException $e) {
            Log::debug('Connection error', $e);
            return false;
        }
        # bad response error
        catch(\GuzzleHttp\Exception\BadResponseException $e) {
            Log::debug('Response error', $e);
            return false;
        }
        # request error
        catch(\GuzzleHttp\Exception\RequestException $e) {
            Log::debug('Request error', $e);
            return false;
        }

        # decode json response
        $json_response = json_decode($response->body(), true);

        # return response
        return $json_response;
    }

    # get price changes (24H)
    public static function getPriceChange($name = "")
    {
        # PRICE CHANGES: {{binance}}/api/v3/ticker/24hr?symbol={{ATAUSDT}}
        try {
            $response = Http::get(env('BINANCE_API')."/api/v3/ticker/24hr?symbol=$name");
        }
        # connection error
        catch(\GuzzleHttp\Exception\ConnectException $e) {
            Log::debug('Connection error', $e);
            return false;
        }
        # bad response error
        catch(\GuzzleHttp\Exception\BadResponseException $e) {
            Log::debug('Response error', $e);
            return false;
        }
        # request error
        catch(\GuzzleHttp\Exception\RequestException $e) {
            Log::debug('Request error', $e);
            return false;
        }

        # decode json response
        $json_response = json_decode($response->body(), true);

        # return response
        return $json_response;
    }

    # analyse crypto
    public static function analyse()
    {
        # get all crypto
        $all_crypto = Crypto::all()->toArray();

        # loop crypto
        foreach($all_crypto as $crypto)
        {
            # get current price changes
            $changes = BinanceService::getPriceChange($crypto["symbol"]);

            # get percent change (without number sign)
            $percent_change = $changes["priceChangePercent"] > 0 ? $changes["priceChangePercent"] : $changes["priceChangePercent"]*(-1);

            # get direction
            $direction = $changes["priceChangePercent"] > 0 ? "up" : "down";

            # get current crypto data
            $current_crypto = [
                "symbol" => $crypto["symbol"],
                "name" => $crypto["name"],
            ];

            # update current crypto data
            $current_crypto = Crypto::where('symbol', $crypto["symbol"])->first();
            $current_crypto->percent_change = $percent_change;
            $current_crypto->direction = $direction;
            $current_crypto->price = $changes["lastPrice"];
            $current_crypto->save();
        }

        # return response
        return true;
    }

    # get good crypto
    public static function getGood($limit=10)
    {
        # get all down crypto
        $crypto = Crypto::where("direction", "down")->get()->toArray();

        # set good crypto
        $good_crypto = [];

        # loop crypto
        foreach($crypto as $current)
        {
            # check percent change
            if($current["percent_change"] > $limit)
            {
                $good_crypto[] = $current;
            }
        }

        # return good crypto
        return $good_crypto;
    }
}
