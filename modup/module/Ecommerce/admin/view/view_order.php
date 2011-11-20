<div id="ecommerce_view_order">
    <div class="info">
        <p>Order Name: <?php echo $eo->order_name; ?></p>
        <p>Order Date: <?php echo date('Y-m-d', $eo->created_date); ?></p>
        <p>Status: <?php echo $eo->Status->name; ?></p>
        <p>Customer Email: <a href="mailto:<?php echo $eo->customer_email; ?>"><?php echo $eo->customer_email; ?></a></p>
        <div class="address cleared">
            <div class="billing">
                <h2>Billing Address</h2>
                <ul>
                    <li><?php echo $eo->BillingAddress->name; ?></li>
                    <li><?php echo $eo->BillingAddress->address1; ?></li>
                    <li><?php echo $eo->BillingAddress->address2; ?></li>
                    <li><?php echo $eo->BillingAddress->city; ?></li>
                    <li><?php echo $eo->BillingAddress->state; ?></li>
                    <li><?php echo $eo->BillingAddress->country; ?></li>
                    <li><?php echo $eo->BillingAddress->zipcode; ?></li>
                    <li><?php echo $eo->BillingAddress->phone; ?></li>
                </ul>
            </div>
            <div class="shipping">
                <h2>Shipping Address</h2>
                <ul>
                    <li><?php echo $eo->ShippingAddress->name; ?></li>
                    <li><?php echo $eo->ShippingAddress->address1; ?></li>
                    <li><?php echo $eo->ShippingAddress->address2; ?></li>
                    <li><?php echo $eo->ShippingAddress->city; ?></li>
                    <li><?php echo $eo->ShippingAddress->state; ?></li>
                    <li><?php echo $eo->ShippingAddress->country; ?></li>
                    <li><?php echo $eo->ShippingAddress->zipcode; ?></li>
                    <li><?php echo $eo->ShippingAddress->phone; ?></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="items">
        <table>
            <thead>
                <tr>
                    <th class="name">Name</th>
                    <th>Weight</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Discount</th>
                    <th>Tax</th>
                    <th>Shipping</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($eo->Products as $product): ?>
                <tr>
                    <td class="name">
                        <p><?php echo $product->name; ?><br />
                        <?php foreach ($product->Options as $option) echo $option->name.': '.$option->data.' '; ?>
                        </p>
                    </td>
                    <td><?php echo $product->weight; ?></td>
                    <td><?php echo $product->price; ?></td>
                    <td><?php echo $product->quantity; ?></td>
                    <td><?php echo $product->discount; ?></td>
                    <td><?php echo $product->tax; ?></td>
                    <td><?php echo $product->shipping; ?></td>
                    <td><?php echo $product->total; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td class="border" colspan="7">Subtotal:</td>
                    <td class="border"><?php echo $eo->subtotal; ?></td>
                </tr>
                <tr>
                    <td colspan="7">Discount:</td>
                    <td><?php echo $eo->discount; ?></td>
                </tr>
                <tr>
                    <td colspan="7">Tax:</td>
                    <td><?php echo $eo->tax; ?></td>
                </tr>
                <tr>
                    <td colspan="7">Shipping:</td>
                    <td><?php echo $eo->shipping; ?></td>
                </tr>
                <tr>
                    <td class="border" colspan="7">Total:</td>
                    <td class="border"><?php echo $eo->total; ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="info">
        <h2>Meta</h2>
        <ul>
            <?php if (count($eo->Options)) foreach ($eo->Options as $meta): ?>
                <li><?php echo ucwords($meta->name); ?> - <?php echo $meta->data; ?></li>
            <?php endforeach; ?>
            <li>User Comments: <?php echo $eo->user_comments; ?></li>
            <li>Admin Comments: <?php echo $eo->admin_comments; ?></li>
            <li>Weight: <?php echo $eo->weight; ?></li>
            <li>PayPal Authorization ID: <?php echo $eo->pp_authorization_id; ?></li>
            <li>PayPal Transaction ID: <?php echo $eo->pp_transaction_id; ?></li>
            <li>Tracking Number: <?php echo $eo->tracking_number; ?></li>
        </ul>
    </div>
</div>
