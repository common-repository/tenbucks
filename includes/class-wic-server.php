<?php
/**
 * Server class.
 *
 * This is used to get iframe url and generate specific token.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Tenbucks
 * @subpackage Tenbucks/includes
 * @author     Web in Color <contact@webincolor.fr>
 */

final class WIC_Server
{
	const URL = 'https://apps.tenbucks.io/dispatch';

	/**
	 * The HASH alorithm to use for oAuth signature, SHA256 or SHA1
	 */
	const HASH_ALGORITHM = 'SHA256';

	/**
	 * Generate token
	 *
	 * @param Array $query
	 * @return string Web Token
	 */
	private static function generateToken(Array $query)
	{
		if (!is_array($query))
			return false;

		$map = array();
		ksort($query);
		foreach ($query as $key => $value)
			$map[] = $key.'='.$value;

		$user_id = get_current_user_id();
		$api_secret = get_user_meta($user_id, 'woocommerce_api_consumer_secret', true);
		return hash_hmac(self::HASH_ALGORITHM, implode('&', $map), $api_secret);
	}

	/**
	 * Retrieve server url
	 *
	 * @param array $query
	 * @param bool $iframe mode iframe
	 * @return string iframe url with query
	 */
	public static function getUrl($path, Array $query, $standalone = false)
	{
		$path = preg_replace('/^\//', '', $path);
		$url = self::URL.$path;

		if ($standalone) {
			$query['standalone'] = true;
		}

		if (count($query))
		{
			// Generate unique token
			$query['token'] = self::generateToken($query);

			$url .= '?'.http_build_query($query);
		}

		return $url;
	}

}
