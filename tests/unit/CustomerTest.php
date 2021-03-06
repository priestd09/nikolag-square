<?php

namespace Nikolag\Square\Tests\Unit;

use Nikolag\Square\Exception;
use Nikolag\Square\Models\Customer;
use Nikolag\Square\Models\Transaction;
use Nikolag\Square\Tests\Models\User;
use Nikolag\Square\Tests\TestCase;
use SquareConnect\ApiException;

class CustomerTest extends TestCase
{

    /**
     * Customer creation.
     *
     * @return void
     */
    public function test_customer_make()
    {
        $customer = factory(Customer::class)->create();

        $this->assertNotNull($customer, 'Customer is null.');
    }

    /**
     * Customer persisting.
     *
     * @return void
     */
    public function test_customer_create()
    {
        $email = $this->faker->email;

        $customer = factory(Customer::class)->create([
            'email' => $email
        ]);

        $this->assertDatabaseHas('nikolag_customers', [
            'email' => $email
        ]);
    }

    /**
     * Listing transcations for customers.
     *
     * @return void
     */
    public function test_customers_have_transactions()
    {
        $user = factory(User::class)->create();
        $customers = factory(Customer::class, 25)
            ->create()
            ->each(function ($customer) {
                $customer->transactions()->save(factory(Transaction::class)->create());
            });
        $customer = $customers->random();

        $this->assertCount(25, Transaction::all(), 'Number of transactions is not 25.');
        $this->assertNotEmpty($customer->transactions, 'Transactions are not empty.');
        $this->assertCount(1, $customer->transactions, 'Transactions count tied with Customer is not 1.');
    }

    /**
     * List transactions.
     *
     * @return void
     */
    public function test_customer_transaction_list()
    {
        $customer = factory(Customer::class)->create();
        $collection = $customer->transactions;

        $this->assertEmpty($collection, 'List of customers is not empty.');
        $this->assertTrue($collection->isEmpty(), 'List of customers is not empty.');
    }

    /**
     * Count passed transactions.
     *
     * @return void
     */
    public function test_customer_passed_transactions()
    {
        $user = factory(User::class)->create();
        $passedTransactions = factory(Transaction::class, 10)->states('PASSED')->create();
        $failedTransactions = factory(Transaction::class, 2)->states('FAILED')->create();
        $openedTransactions = factory(Transaction::class, 2)->states('OPENED')->create();

        $user->transactions()->saveMany($openedTransactions);
        $user->transactions()->saveMany($failedTransactions);
        $user->transactions()->saveMany($passedTransactions);

        $this->assertCount(10, $user->passedTransactions, 'Passed transactions count tied with User is not 10.');
        $this->assertCount(14, $user->transactions, 'Transactions count tied with User is not 14.');
    }

    /**
     * Count failed transactions.
     *
     * @return void
     */
    public function test_customer_failed_transactions()
    {
        $user = factory(User::class)->create();
        $failedTransactions = factory(Transaction::class, 2)->states('FAILED')->create();
        $openedTransactions = factory(Transaction::class)->states('OPENED')->create();
        $passedTransactions = factory(Transaction::class)->states('PASSED')->create();

        $user->transactions()->save($openedTransactions);
        $user->transactions()->saveMany($failedTransactions);
        $user->transactions()->save($passedTransactions);

        $this->assertCount(2, $user->failedTransactions, 'Failed transactions count tied with User is not 2.');
        $this->assertCount(4, $user->transactions, 'Transactions count tied with User is not 4.');
    }

    /**
     * Count opened transactions.
     *
     * @return void
     */
    public function test_customer_opened_transactions()
    {
        $user = factory(User::class)->create();
        $openedTransactions = factory(Transaction::class, 5)->states('OPENED')->create();
        $failedTransactions = factory(Transaction::class, 2)->states('FAILED')->create();
        $passedTransactions = factory(Transaction::class)->states('PASSED')->create();

        $user->transactions()->saveMany($openedTransactions);
        $user->transactions()->saveMany($failedTransactions);
        $user->transactions()->save($passedTransactions);

        $this->assertCount(5, $user->openedTransactions, 'Opened transactions count tied with User is not 5.');
        $this->assertCount(8, $user->transactions, 'Transactions count tied with User is not 8.');
    }

    /**
     * Count scoped queries for different
     * transaction statuses.
     *
     * @return void
     */
    public function test_customer_transactions_statuses()
    {
        $user = factory(User::class)->create();
        $openedTransactions = factory(Transaction::class, 5)->states('OPENED')->create();
        $failedTransactions = factory(Transaction::class, 2)->states('FAILED')->create();
        $passedTransactions = factory(Transaction::class)->states('PASSED')->create();

        $user->transactions()->saveMany($openedTransactions);
        $user->transactions()->saveMany($failedTransactions);
        $user->transactions()->save($passedTransactions);

        $this->assertCount(5, $user->openedTransactions, 'Opened transactions count tied with User is not 5.');
        $this->assertCount(2, $user->failedTransactions, 'Failed transactions count tied with User is not 2.');
        $this->assertCount(1, $user->passedTransactions, 'Passed transactions count tied with User is not 1.');
        $this->assertCount(8, $user->transactions, 'Transactions count tied with User is not 8.');
    }
}