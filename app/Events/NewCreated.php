<?php

namespace App\Events;

use App\Models\MasterfileModels\Checker;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public $type) {}


    public function broadcastOn()
    {
        if ($this->type === "checker") {
            return new Channel('checkers');
        } else if ($this->type === 'user') {
            return new Channel('users');
        } else if ($this->type === 'item') {
            return new Channel('items');
        } else if ($this->type === 'adjustment_reason_setup') {
            return new Channel('adjustment_reason_setups');
        } else if ($this->type === 'cash_in_bank') {
            return new Channel('cash_in_banks');
        } else if ($this->type === 'charge_invoice_type') {
            return new Channel('charge_invoice_types');
        } else if ($this->type === 'packing_type') {
            return new Channel('packing_types');
        } else if ($this->type === 'shortage_amount') {
            return new Channel('shortage_amounts');
        } else if ($this->type === 'invoice') {
            return new Channel('invoices');
        } else if ($this->type === 'adjustment') {
            return new Channel('adjustments');
        } else if ($this->type === 'payment') {
            return new Channel('payments');
        } else if ($this->type === 'beginningbalance') {
            return new Channel('beginningbalances');
        } else if ($this->type === 'customerledger') {
            return new Channel('customerledgers');
        } else if ($this->type === 'check_clearing') {
            return new Channel('check_clearings');
        } else if ($this->type === 'wht_clearing') {
            return new Channel('wht_clearings');
        } else if ($this->type === 'cancel_payment') {
            return new Channel('cancel_payments');
        } else if ($this->type === 'notif') {
            return new Channel('notifs');
        }
    }

    public function broadcastAs()
    {
        if ($this->type === "checker") {
            return 'checker.created';
        } else if ($this->type === 'user') {
            return 'user.created';
        } else if ($this->type === 'item') {
            return 'item.created';
        } else if ($this->type === 'adjustment_reason_setup') {
            return 'adjustment_reason_setup.created';
        } else if ($this->type === 'cash_in_bank') {
            return 'cash_in_bank.created';
        } else if ($this->type === 'charge_invoice_type') {
            return 'charge_invoice_type.created';
        } else if ($this->type === 'packing_type') {
            return 'packing_type.created';
        } else if ($this->type === 'shortage_amount') {
            return 'shortage_amount.created';
        } else if ($this->type === 'invoice') {
            return 'invoice.created';
        } else if ($this->type === 'adjustment') {
            return 'adjustment.created';
        } else if ($this->type === 'payment') {
            return 'payment.created';
        } else if ($this->type === 'beginningbalance') {
            return 'beginningbalance.created';
        } else if ($this->type === 'customerledger') {
            return 'customerledger.created';
        } else if ($this->type === 'check_clearing') {
            return 'check_clearing.created';
        } else if ($this->type === 'wht_clearing') {
            return 'wht_clearing.created';
        } else if ($this->type === 'cancel_payment') {
            return 'cancel_payment.created';
        } else if ($this->type === 'notif') {
            return 'notif.created';
        }
    }

    public function broadcastWith()
    {
        if ($this->type === "checker") {
            return [
                'message' => 'New checker created'
            ];
        } else if ($this->type === 'user') {
            return [
                'message' => 'New user created'
            ];
        } else if ($this->type === 'item') {
            return [
                'message' => 'New item created'
            ];
        } else if ($this->type === 'adjustment_reason_setup') {
            return [
                'message' => 'New adjustment reason setup created'
            ];
        } else if ($this->type === 'cash_in_bank') {
            return [
                'message' => 'New cash in bank created'
            ];
        } else if ($this->type === 'charge_invoice_type') {
            return [
                'message' => 'New charge invoice type created'
            ];
        } else if ($this->type === 'packing_type') {
            return [
                'message' => 'New packing type created'
            ];
        } else if ($this->type === 'shortage_amount') {
            return [
                'message' => 'New shortage amount created'
            ];
        } else if ($this->type === 'invoice') {
            return [
                'message' => 'New invoice created'
            ];
        } else if ($this->type === 'adjustment') {
            return [
                'message' => 'New adjustment created'
            ];
        } else if ($this->type === 'payment') {
            return [
                'message' => 'New payment created'
            ];
        } else if ($this->type === 'beginningbalance') {
            return [
                'message' => 'New beginning balance created'
            ];
        } else if ($this->type === 'customerledger') {
            return [
                'message' => 'New customer ledger created'
            ];
        } else if ($this->type === 'check_clearing') {
            return [
                'message' => 'New check clearing created'
            ];
        } else if ($this->type === 'wht_clearing') {
            return [
                'message' => 'New wht clearing created'
            ];
        } else if ($this->type === 'cancel_payment') {
            return [
                'message' => 'New cancel payment created'
            ];
        } else if ($this->type === 'notif') {
            return [
                'message' => 'Notifications Updated'
            ];
        }
    }
}
