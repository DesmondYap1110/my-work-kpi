<?php



function Commission($amount)
{
    if($amount<2000)
    {
        $commision = 0;
    }
    elseif($amount>=2000 && $amount<2999.99)
    {
        $commision = 20;
    }
    elseif($amount>=3000)
    {
        $commision = 50;
    }
    
    return $commision;
}

function Cbonus($rating,$commission)
{
    $total = 0;
    
    if($rating>=0 && $rating<3)
    {
        $total = 0;
    }
    elseif($rating>=3 && $rating<6)
    {
        $total = ($commission*0.5);
    }
    elseif($rating>=6 && $rating<9)
    {
        $total = ($commission*0.75);
    }
    elseif($rating>=9 && $rating<11)
    {
        $total = $commission;
    }
    
    return $total;
}

function bonus($rating,$commission)
{
    $total = 0;
    
    if($rating>=0 && $rating<3)
    {
        $total = $commission;
    }
    elseif($rating>=3 && $rating<6)
    {
        $total = $commission + ($commission*0.5);
    }
    elseif($rating>=6 && $rating<9)
    {
        $total = $commission + ($commission*0.75);
    }
    elseif($rating>=9 && $rating<11)
    {
        $total = $commission + $commission;
    }
    
    return $total;
}











?>