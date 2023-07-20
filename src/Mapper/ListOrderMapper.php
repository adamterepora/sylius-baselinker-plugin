<?php

/**
 * @author Adam Terepora <adam@terepora.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spinbits\SyliusBaselinkerPlugin\Mapper;

use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Core\OrderPaymentStates;
use Symfony\Component\Intl\Countries;

class ListOrderMapper
{
	public function map(Order $order, ChannelInterface $channel): array
	{
		$products = [];
		/** @var OrderItemInterface $orderItem */
		foreach ($order->getItems() as $orderItem) {
			/** @var ProductVariant $v */
			$v = $orderItem->getVariant();
			$taxAdjustments = $orderItem->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT);
			foreach ($taxAdjustments as $taxAdjustment) {
				$t = '';
			}
			$product = [
				'id'         => $orderItem->getProduct()->getId(),
				'name'       => sprintf('%s (%s)', $orderItem->getProductName(), $orderItem->getVariantName()),
				'quantity'   => $orderItem->getQuantity(),
				'price'      => $orderItem->getFullDiscountedUnitPrice() / 100,
				'tax'        => 23, // here should be proper order item tax rate
				'weight'     => 0,
				'sku'        => $orderItem->getVariant()->getCode(),
				'ean'        => null,
				'attributes' => [],
			];

			array_push($products, $product);
		}


		return [
			'delivery_fullname'     => $order->getShippingAddress()->getFullName(),
			'delivery_company'      => $order->getShippingAddress()->getCompany(),
			'delivery_address'      => $order->getShippingAddress()->getStreet(),
			'delivery_city'         => $order->getShippingAddress()->getCity(),
			'delivery_postcode'     => $order->getShippingAddress()->getPostcode(),
			'delivery_country'      => Countries::getName($order->getShippingAddress()->getCountryCode()),
			'delivery_country_code' => $order->getShippingAddress()->getCountryCode(),
			'invoice_fullname'      => $order->getBillingAddress()->getFullName(),
			'invoice_company'       => $order->getBillingAddress()->getCompany(),
			'invoice_nip'           => '',
			'invoice_address'       => $order->getBillingAddress()->getStreet(),
			'invoice_city'          => $order->getBillingAddress()->getCity(),
			'invoice_postcode'      => $order->getBillingAddress()->getPostcode(),
			'invoice_country'       => Countries::getName($order->getBillingAddress()->getCountryCode()),
			'invoice_country_code'  => $order->getBillingAddress()->getCountryCode(),
			'phone'                 => $order->getCustomer()->getPhoneNumber(),
			'email'                 => $order->getCustomer()->getEmail(),
			'want_invoice'          => (int)false, // will be replaced with checking whether billing address provided and tax number provided
			'date_add'              => $order->getCheckoutCompletedAt()->format('U'),
			'user_comments'         => $order->getNotes(),
			'delivery_method'       => $order->getShipments()->count() > 0 ? $order->getShipments()->last()->getMethod()->getName() : '',
			'payment_method'        => $order->getLastPayment()->getMethod()->getName(),
			'payment_method_cod'    => (int) $order->getLastPayment()->getMethod()->getCode() === 'cash_on_delivery',
			'delivery_price'        => $order->getShippingTotal() / 100,
			'currency'              => $order->getLastPayment()->getCurrencyCode(),
			'status_id'             => $order->getCheckoutState(),
			'paid'                  => (int) $order->getPaymentState() === OrderPaymentStates::STATE_PAID,
			'products'              => $products,
		];
	}
}
