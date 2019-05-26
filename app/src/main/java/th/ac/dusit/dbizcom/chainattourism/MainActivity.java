package th.ac.dusit.dbizcom.chainattourism;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;

import com.bumptech.glide.request.RequestOptions;
import com.glide.slider.library.Animations.DescriptionAnimation;
import com.glide.slider.library.SliderLayout;
import com.glide.slider.library.SliderTypes.BaseSliderView;
import com.glide.slider.library.SliderTypes.DefaultSliderView;
import com.glide.slider.library.Tricks.ViewPagerEx;

import java.util.ArrayList;

public class MainActivity extends BaseActivity implements
        BaseSliderView.OnSliderClickListener,
        ViewPagerEx.OnPageChangeListener, View.OnClickListener {

    private SliderLayout mSlider;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        setupImageSlider();
        findViewById(R.id.place_layout).setOnClickListener(this);
        findViewById(R.id.temple_layout).setOnClickListener(this);
        findViewById(R.id.restaurant_layout).setOnClickListener(this);
        findViewById(R.id.otop_layout).setOnClickListener(this);
    }

    private void setupImageSlider() {
        mSlider = findViewById(R.id.slider);

        ArrayList<String> listUrl = new ArrayList<>();
        listUrl.add("http://5911011802058.msci.dusit.ac.th/chainat_tourism/images/hero01.png");
        listUrl.add("http://5911011802058.msci.dusit.ac.th/chainat_tourism/images/hero02.png");

        RequestOptions requestOptions = new RequestOptions().fitCenter();

        //.diskCacheStrategy(DiskCacheStrategy.NONE)
        //.placeholder(R.drawable.placeholder)
        //.error(R.drawable.placeholder);

        for (int i = 0; i < listUrl.size(); i++) {
            DefaultSliderView sliderView = new DefaultSliderView(this);
            sliderView
                    .image(listUrl.get(i))
                    .setRequestOption(requestOptions)
                    //.setBackgroundColor(Color.WHITE)
                    .setProgressBarVisible(true)
                    .setOnSliderClickListener(this);

            //add your extra information
            sliderView.bundle(new Bundle());
            //sliderView.getBundle().putString("extra", listName.get(i));
            mSlider.addSlider(sliderView);
        }

        // set Slider Transition Animation
        // mSlider.setPresetTransformer(SliderLayout.Transformer.Default);
        mSlider.setPresetTransformer(SliderLayout.Transformer.Accordion);

        mSlider.setPresetIndicator(SliderLayout.PresetIndicators.Center_Bottom);
        mSlider.setCustomAnimation(new DescriptionAnimation());
        mSlider.setDuration(3000);
        mSlider.addOnPageChangeListener(this);
    }

    @Override
    protected void onStop() {
        mSlider.stopAutoCycle();
        super.onStop();
    }

    @Override
    public void onClick(View view) {
        switch (view.getId()) {
            case R.id.place_layout:
                Intent intent = new Intent(MainActivity.this, PlaceActivity.class);
                startActivity(intent);
                break;
            case R.id.temple_layout:
                break;
            case R.id.restaurant_layout:
                break;
            case R.id.otop_layout:
                break;
        }
    }

    @Override
    public void onSliderClick(BaseSliderView slider) {

    }

    @Override
    public void onPageScrolled(int position, float positionOffset, int positionOffsetPixels) {

    }

    @Override
    public void onPageSelected(int position) {

    }

    @Override
    public void onPageScrollStateChanged(int state) {

    }
}
